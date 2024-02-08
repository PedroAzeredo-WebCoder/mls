require("jquery");
require("bootstrap");
require("@fortawesome/fontawesome-free");
require("slick-carousel");
require("jquery-mask-plugin/dist/jquery.mask.min");
import AOS from "aos";
import Quill from "quill";
import Chart from "chart.js";
require("sweetalert2/dist/sweetalert2.all.min");
require("jquery-maskmoney/dist/jquery.maskMoney.min");
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";

window._ = require("lodash");

try {
  window.Popper = require("@popperjs/core").default;
  window.$ = window.jQuery = require("jquery");
} catch (e) {}

jQuery(function () {
  masks();
  consultaCep();
  themeColor();
  switchChange();

  AOS.init();

  $(window).ready(function () {
    masks();
    consultaCep();
    themeColor();
    addShowPasswordButtons();
    AOS.init();
  });

  $(window).on("load", function () {
    masks();
    consultaCep();
    themeColor();

    AOS.init();
  });

  function masks() {
    //Telefone
    let phoneFields = $(
      '[type="tel"], [id="tel"], [name="f_telefone"], [name="f_celular"]'
    );

    let phoneMaskBehavior = function (val) {
      if (val !== undefined && val !== null) {
        return val.replace(/\D/g, "").length === 11
          ? "(00) 00000-0000"
          : "(00) 0000-00009";
      }
    };

    let phoneOptions = {
      onKeyPress: function (val, e, field, options) {
        field.mask(phoneMaskBehavior.apply({}, arguments), options);
      },
    };
    phoneFields.mask(phoneMaskBehavior, phoneOptions);
    phoneFields.attr("inputmode", "numeric");

    //CEP
    let cepFields = $('[name="cep"], [id="cep"], [name="f_cep"]');
    cepFields.mask("00000-000", {
      reverse: true,
    });
    cepFields.attr("inputmode", "numeric");

    //CPF
    let cpf = $('.mask-cpf, [name="cpf"], [id="cpf"], [name="f_cpf"]');
    cpf.mask("000.000.000-00", {
      reverse: true,
    });
    cpf.attr("inputmode", "numeric");

    //CNPJ
    let cnpj = $('.mask-cnpj, [name="cnpj"], [id="cnpj"], [name="f_cnpj"]');
    cnpj.mask("00.000.000/0000-00", {
      reverse: true,
    });
    cnpj.attr("inputmode", "numeric");

    //CPF ou CNPJ
    let cpfCnpj = $('[name="cpf-cnpj"], [id="cpf-cnpj"], [name="f_documento"]');

    let options = {
      onKeyPress: function (cpf, ev, el, op) {
        let masks = ["000.000.000-00#", "00.000.000/0000-00"];
        let sanitizedValue = cpfCnpj.val()?.replace(/\D/g, "");
        cpfCnpj.mask(sanitizedValue.length > 11 ? masks[1] : masks[0], op);
      },
    };

    let sanitizedValue = cpfCnpj.val();
    if (sanitizedValue !== undefined && sanitizedValue !== null) {
      cpfCnpj.mask(
        sanitizedValue.length > 14 ? "00.000.000/0000-00" : "000.000.000-00#",
        options
      );
    }

    cpfCnpj.attr("inputmode", "numeric");

    //Números cartão de crédito
    let creditCard = $(
      '.mask-credit-card, [name="credit-card"], [id="credit-card"]'
    );
    creditCard.mask("0000 0000 0000 0000", {
      reverse: true,
    });
    creditCard.attr("inputmode", "numeric");

    //CVV
    let cvv = $('.mask-cvv, [name="cvv"], [id="cvv"]');
    cvv.mask("000", {
      reverse: true,
    });
    cvv.attr("inputmode", "numeric");

    //Data
    // let date = $('.mask-date, [name="date"], [id="date"], [type="date"]')
    // date.mask('00/00/0000', {
    //   reverse: true
    // })
    // date.attr('inputmode', 'numeric')

    //Mes e ano
    let mesAno = $('.mask-mesAno, [name="mesAno"], [id="mesAno"]');
    mesAno.mask("00/0000", {
      reverse: true,
    });
    mesAno.attr("inputmode", "numeric");

    let ano = $(
      '.mask-mesAno, [name="mesAno"], [id="mesAno"], [name="f_ano_fabricacao"], [name="f_ano_veiculo"]'
    );
    ano.mask("0000", {
      reverse: true,
    });
    mesAno.attr("inputmode", "numeric");

    let money = $(
      '[name="f_valor"], [name="f_preco"], [name="f_valor_unitario"], [name="f_valor_original"]'
    );
    money.attr("inputmode", "numeric");
    money.maskMoney({
      prefix: "R$ ",
      defaultValue: "R$ 0,00",
      allowNegative: true,
      thousands: ".",
      decimal: ",",
      affixesStay: true,
    });

    let porcentage = $('[name="f_porcentagem"],[name="porcentagem"]');
    porcentage.attr("inputmode", "numeric");
    porcentage.mask("##0,00%", {
      reverse: true,
    });

    // var placaInput = $('[name="f_placa"],[name="placa"]');

    // placaInput.on("input change", function () {
    //   var placa = $(this).val().toUpperCase();
    //   var pattern_antigo = /[A-Z]{3}-\d{4}/;
    //   var pattern_mercosul = /[A-Z]{3}\d[A-Z]\d{2}/;

    //   if (pattern_antigo.test(placa)) {
    //     $(this).mask("000-0000");
    //   } else if (pattern_mercosul.test(placa)) {
    //     $(this).mask("0000000");
    //   } else {
    //     $(this).unmask();
    //   }
    // });

    // placaInput.on("paste", function () {
    //   var self = this;
    //   setTimeout(function () {
    //     $(self).trigger("input");
    //   }, 100);
    // });

    // placaInput.trigger("change");
  }

  function consultaCep() {
    const elementos = [
      {
        name: "uf",
        selector: "#js_estado",
      },
      {
        name: "bairro",
        selector: "#js_bairro",
      },
      {
        name: "logradouro",
        selector: "#js_logradouro",
      },
      {
        name: "localidade",
        selector: "#js_cidade",
      },
      {
        selector: "#js_numero",
      },
      {
        selector: "#js_complemento",
      },
    ];

    const campos = elementos.map((elemento) => {
      return $(
        `[name="f_${elemento.name}"],[name="${elemento.name}"],${elemento.selector}`
      );
    });

    const mostrarCampos = () =>
      campos.forEach((campo) => campo.removeClass("d-none"));

    const ocultarCampos = () =>
      campos.forEach((campo) => campo.addClass("d-none"));

    const preencherCampos = (valor) => {
      elementos.forEach(({ name, selector }) => {
        const campo = $(`[name="f_${name}"],[name="${name}"],${selector}`);
        campo.val(valor[name]);
      });
    };

    const mostrarErro = () => {
      swal({
        title: "CEP inválido!",
        icon: "error",
        timer: 3000,
      });
    };

    const buscarCep = (cep) => {
      return $.getJSON(`https://viacep.com.br/ws/${cep}/json/`);
    };

    const onCepChange = function () {
      const cep = $(this).val();
      if (!cep) return;

      const consultaCep = buscarCep(cep);

      consultaCep.always(() => {
        const valor = consultaCep.responseJSON;

        if (!("erro" in valor)) {
          mostrarCampos();
          preencherCampos(valor);
        } else {
          ocultarCampos();
          mostrarErro();
        }
      });
    };

    ocultarCampos();
    $('.mask-cep, [name="cep"], [id="cep"],[name="f_cep"]').on(
      "input",
      onCepChange
    );

    // chama a função onCepChange quando a página é carregada
    const cepInicial = $('[name="f_cep"]').val();
    if (cepInicial) onCepChange.call($('[name="f_cep"]')[0]);
  }

  function addShowPasswordButton(element) {
    const id = element.attr("id");
    element.after(
      `<span class='showPass' toggle='#${id}'><svg viewBox='0 0 24 24' width='24' height='24' stroke='#000' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round' class='css-i6dzq1'><path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle></svg></span>`
    );
  }

  function togglePassword(input, button) {
    const inputType = input.attr("type");
    if (inputType === "password") {
      input.attr("type", "text");
      button
        .children("svg")
        .html(
          '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
        );
    } else {
      input.attr("type", "password");
      button
        .children("svg")
        .html(
          '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
        );
    }
  }

  function addShowPasswordButtons() {
    const passwordInputs = $("[type='password'][id]");
    passwordInputs.each(function () {
      addShowPasswordButton($(this));
    });
    $(".showPass").click(function () {
      const input = $($(this).attr("toggle"));
      togglePassword(input, $(this));
    });
  }

  // add theme-all-scripts main color in header meta tags
  function themeColor() {
    const themeColor = $(
      '[name="theme-color"], [name="msapplication-navbutton-color"], [name="msapplication-TileColor"]'
    );
    const value = getComputedStyle(document.documentElement).getPropertyValue(
      "--bs-primary"
    );
    themeColor.attr("content", value);
  }

  function switchChange() {
    $("#id_swithChangeOn").on("change", function () {
      $("#searchTable form").submit();
    });
  }

  function initializeEditors() {
    $(".editor-field").each(function () {
      const $editor = $(this);
      const toolbarOptions = $editor.data("toolbaroptions")
        ? JSON.parse($editor.data("toolbaroptions"))
        : null;
      const quill = new Quill(this, {
        modules: {
          toolbar: toolbarOptions || [
            ["bold", "italic", "underline", "strike"],
            ["blockquote", "code-block"],
            [
              {
                header: 1,
              },
              {
                header: 2,
              },
            ],
            [
              {
                list: "ordered",
              },
              {
                list: "bullet",
              },
            ],
            [
              {
                script: "sub",
              },
              {
                script: "super",
              },
            ],
            [
              {
                indent: "-1",
              },
              {
                indent: "+1",
              },
            ],
            [
              {
                direction: "rtl",
              },
            ],
            [
              {
                size: ["small", false, "large", "huge"],
              },
            ],
            [
              {
                header: [1, 2, 3, 4, 5, 6, false],
              },
            ],
            [
              {
                color: [],
              },
              {
                background: [],
              },
            ],
            [
              {
                font: [],
              },
            ],
            [
              {
                align: [],
              },
            ],
            ["clean"],
          ],
        },
        theme: "snow",
      });

      const $targetInput = $(`#${$editor.data("target")}`);

      if ($targetInput.val()) {
        quill.root.innerHTML = $targetInput.val();
      }

      quill.on("text-change", function () {
        $targetInput.val(quill.root.innerHTML);
      });

      quill.keyboard.addBinding(
        {
          key: "Enter",
        },
        function (range, context) {
          if (context.format.lineHeight !== "normal") {
            this.quill.insertText(range.index, "\n", Quill.sources.USER);
            this.quill.setSelection(range.index + 1, Quill.sources.SILENT);
          } else {
            return true;
          }
        }
      );

      quill.on("keydown", function (event) {
        if (event.key === "Enter" && !event.shiftKey) {
          event.preventDefault();
        }
      });
    });
  }

  // Exemplo de uso
  initializeEditors();

  // Editor personalizado
  const toolbarOptionsCustom = JSON.stringify([
    ["bold", "italic", "underline", "strike"],
    ["blockquote", "code-block"],
    [
      {
        header: 1,
      },
      {
        header: 2,
      },
    ],
    [
      {
        list: "ordered",
      },
      {
        list: "bullet",
      },
    ],
    [
      {
        script: "sub",
      },
      {
        script: "super",
      },
    ],
    [
      {
        indent: "-1",
      },
      {
        indent: "+1",
      },
    ],
    [
      {
        direction: "rtl",
      },
    ],
    [
      {
        size: ["small", false, "large", "huge"],
      },
    ],
    [
      {
        header: [1, 2, 3, 4, 5, 6, false],
      },
    ],
    [
      {
        color: [],
      },
      {
        background: [],
      },
    ],
    [
      {
        font: [],
      },
    ],
    [
      {
        align: [],
      },
    ],
    ["clean"],
  ]);

  const editorCustom = document.querySelector("#editor-custom");
  if (editorCustom) {
    editorCustom.dataset.toolbaroptions = toolbarOptionsCustom;
    initializeEditors();
  }

  $(document).ready(function () {
    $(".copy").on("click", function () {
      var conteudo = $(".ql-editor p").text();

      // Tenta copiar o conteúdo para a área de transferência usando o método writeText
      navigator.clipboard
        .writeText(conteudo)
        .then(function () {
          console.log("Copiado para a área de transferência!");
        })
        .catch(function (err) {
          console.error("Erro ao copiar para a área de transferência:", err);
        })
        .finally(function () {
          // Altera o texto do botão para 'Copiado!'
          $(".copy").text("Copiado!");
        });
    });
  });

  $(document).ready(function () {
    // Quando um botão ou input do tipo submit for clicado
    $('button[type="submit"], input[type="submit"]').click(function () {
      // Armazena o elemento clicado em uma variável
      var $button = $(this);

      // Armazena o texto atual do botão em uma variável
      var buttonText = $button.text();

      // Substitui o texto do botão pelo elemento HTML com a classe "spinner-border"
      $button.html(
        '<div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div>'
      );

      // Simula um atraso de 3 segundos para demonstração
      setTimeout(function () {
        // Restaura o texto original do botão após o atraso de 3 segundos
        $button.text(buttonText);
      }, 3000);
    });
  });

  $(document).ready(function () {
    var select = $("#inputState");
    var form = $("#searchTable");

    function updateFormAction() {
      var selectedValue = select.val();
      form.attr("action", selectedValue);
    }

    if (select.val()) {
      updateFormAction();
    }

    select.on("change", function () {
      updateFormAction();
    });
  });

  $(document).ready(function () {
    function toggleSwitchLabel(fieldset) {
      const checkbox = fieldset.find("input[type='checkbox']");
      const labels = fieldset.data("labels").split(",");

      // Verifica se há exatamente dois labels
      if (labels.length === 2) {
        // Obtém o estado atual do switch (checked ou não)
        const isChecked = checkbox.prop("checked");

        // Se o switch estiver ligado (checked), exibe o primeiro label
        // Caso contrário, exibe o segundo label
        const labelToShow = isChecked ? labels[0] : labels[1];

        // Atualiza o texto do label do switch
        fieldset.find("label").text(labelToShow);
      }
    }

    // Adiciona o evento de clique para o switch
    $("[data-labels]").on("click", function () {
      toggleSwitchLabel($(this));
    });

    // Verifica o estado inicial do switch no carregamento da página
    $("[data-labels]").each(function () {
      toggleSwitchLabel($(this));
    });
  });

  $(function () {
    $(".itens-content").each(function () {
      const content = $(this);
      const group = content.find(".input-group");
      const quantityInput = group.find(".quantity-input");
      const minValue = parseInt(quantityInput.attr("min"));

      function updateValor(input) {
        let quantidade = parseInt(input.val());
        if (isNaN(quantidade) || quantidade < minValue) {
          quantidade = minValue;
        }
        input.val(quantidade);
      }

      function increaseQuantity(input) {
        let value = parseInt(input.val());
        const maxQuantity = parseInt(input.data("quantidade"));

        // Verifica se há um limite máximo
        if (isNaN(maxQuantity) || value < maxQuantity) {
          input.val(value + 1);
          updateValor(input);
        }
      }

      function decreaseQuantity(input) {
        let value = parseInt(input.val());
        if (value > minValue) {
          input.val(value - 1);
          updateValor(input);
        }
      }

      // Adicionando eventos aos botões de aumento e redução
      content.on("click", ".plus-btn", function () {
        const input = $(this).siblings(".quantity-input");
        increaseQuantity(input);
      });

      content.on("click", ".subtract-btn", function () {
        const input = $(this).siblings(".quantity-input");
        decreaseQuantity(input);
      });

      // Atualizando valor inicial
      updateValor(quantityInput);
    });
  });

  $(document).ready(function () {
    function validateHorarioFinal($inicial, $final) {
      if ($inicial.val() !== "") {
        $final.prop("required", true);
        $final.prop("min", $inicial.val());
      } else {
        $final.prop("required", false);
        $final.prop("min", "");
      }
    }

    function validateHorarioInicial($inicial, $final) {
      var initialValue = $inicial.val();
      var finalValue = $final.val();

      if (finalValue < initialValue) {
        $final.val(initialValue);
      }

      validateHorarioFinal($inicial, $final);
    }

    $(document).on("input", ".horario-input[name$='_inicial']", function () {
      var $inicial = $(this);
      var $final = $(
        ".horario-input[name='" + this.name.replace("_inicial", "_final") + "']"
      );
      validateHorarioInicial($inicial, $final);
    });

    $(".horario-input[name$='_inicial']").each(function () {
      var $inicial = $(this);
      var $final = $(
        ".horario-input[name='" + this.name.replace("_inicial", "_final") + "']"
      );
      validateHorarioInicial($inicial, $final);
    });
  });

  const $passwordInput = document.querySelector(".password-input");
  const progressBar = document.querySelector(".progress .progress-bar");

  function calculatePasswordStrength(password) {
    let strength = 0;

    // Teste de comprimento mínimo
    if (password.length >= 8) {
      strength += 25;
    }

    // Teste de caracteres maiúsculos e minúsculos
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
      strength += 25;
    }

    // Teste de números
    if (/\d/.test(password)) {
      strength += 25;
    }

    // Teste de símbolos
    if (/[^a-zA-Z\d]/.test(password)) {
      strength += 25;
    }

    return strength;
  }

  function getPasswordStrengthLevel(strength) {
    if (strength <= 25) {
      return "Fraca";
    } else if (strength <= 50) {
      return "Média";
    } else if (strength <= 75) {
      return "Forte";
    } else {
      return "Muito Forte";
    }
  }

  $passwordInput.addEventListener("input", function () {
    let strength = calculatePasswordStrength($passwordInput.value);
    let passwordStrength = Math.min(100, Math.max(0, strength));

    progressBar.style.width = passwordStrength + "%";
    const level = getPasswordStrengthLevel(strength);
    progressBar.textContent = level;
  });

  // Registrar os plugins do FilePond
  FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginImageEdit
  );

  // Criar o FilePond
  const inputElement = document.querySelector(".filepond");
  const pond = FilePond.create(inputElement, {
    labelIdle: `<i class="ph ph-camera"></i></br> Arraste e solte sua foto ou navegue <span class="filepond--label-action"></span>`,
    imagePreviewHeight: 170,
    imageCropAspectRatio: "1:1",
    imageResizeTargetWidth: 200,
    imageResizeTargetHeight: 200,
    stylePanelLayout: "compact circle",
    styleLoadIndicatorPosition: "center bottom",
    styleProgressIndicatorPosition: "left bottom",
    styleButtonRemoveItemPosition: "center bottom",
    styleButtonProcessItemPosition: "right bottom",
    allowImagePreview: true,
    allowFileRename: true,
    allowRemoveFiles: true,
    allowImageEdit: true,
    allowEdit: true,
    maxFiles: 10,
    oninit: () => {
      const base64Image = document.querySelector(".filepond-value").value;
      const blob = b64toBlob(base64Image);
      const file = new File([blob], "uploaded-image.png", {
        type: "image/png",
      });
      pond.addFile(file);
    },
  });

  // Função para converter base64 para Blob
  function b64toBlob(b64Data, contentType = "", sliceSize = 512) {
    const byteCharacters = atob(b64Data);
    const byteArrays = [];
    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
      const slice = byteCharacters.slice(offset, offset + sliceSize);
      const byteNumbers = new Array(slice.length);
      for (let i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
      }
      const byteArray = new Uint8Array(byteNumbers);
      byteArrays.push(byteArray);
    }
    const blob = new Blob(byteArrays, {
      type: contentType,
    });
    return blob;
  }
});

$(document).ready(function () {
  // Função para atualizar a visibilidade do botão de remover
  function updateRemoveButtonVisibility() {
    $(".itens-content").each(function () {
      var itemCount = $(this).find(".itens-list").length;
      if (itemCount > 1) {
        $(this).find(".remove-item").removeClass("d-none");
      } else {
        $(this).find(".remove-item").addClass("d-none");
      }
    });
  }

  // Oculta o botão de remover inicialmente se houver apenas um item
  updateRemoveButtonVisibility();

  // Adiciona um novo item quando o botão add-item for clicado
  $(document).on("click", ".add-item", function () {
    var previousItem = $(this)
      .closest(".itens-content")
      .find(".itens-list:last");
    var newItem = previousItem.clone();
    previousItem.after(newItem);
    
    // Atualiza a visibilidade do botão de remover
    updateRemoveButtonVisibility();
  });

  // Remove o item quando o botão remove-item for clicado
  $(document).on("click", ".remove-item", function () {
    $(this).closest(".itens-list").remove();
    
    // Atualiza a visibilidade do botão de remover
    updateRemoveButtonVisibility();
  });
});


