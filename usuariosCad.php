<?php
require_once("./inc/common.php");
checkAccess("usuariosList");

$e = getParam("e", true);
$cad_usuario_id = $e["cad_usuario_id"];

$f_status = "checked";

if (!empty($cad_usuario_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_usuarios");
    $query->addcolumn("id");
    $query->addcolumn("nome");
    $query->addcolumn("imagem");
    $query->addcolumn("email");
    $query->addcolumn("cad_cargo_id");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_usuario_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_email = $row["email"];        
        $cad_cargo_id = $row["cad_cargo_id"];
        $f_imagem = $row["imagem"];
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }
}

$cargos = new sqlQuery();
$cargos->addTable("cad_cargos");
$cargos->addcolumn("id");
$cargos->addcolumn("nome");
$cargos->addWhere("status", "=", "1");

if ($conn->query($cargos->getSQL()) && getDbValue($cargos->getCount()) != 0) {
    foreach ($conn->query($cargos->getSQL()) as $row) {
        $options_f_cargos[] = array("id" => $row["id"], "name" => $row["nome"]);
    }
} else {
    $options_f_cargos[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
}

$form = new Form("usuariosCadSave.php");
$form->setUpload(true);
$form->addField(hiddenField($cad_usuario_id, "cad_usuario_id"));
$form->addField(fileField("Foto de Perfil", '', "f_imagem", false, true, "image/png, image/jpeg, image/jpg"));
$form->addField(textField("Nome", $f_nome, NUll, true));
$form->addField(emailField("E-mail", $f_email, NUll, true, "^(?=.{1,256})(?=.{1,64}@)[^\s@]+@[^\s@]+\.[^\s@]{2,}$", NULL, "text-lowercase"));
$form->addField(listField("Cargo", $options_f_cargos, $cad_cargo_id, NULL, true));

if (!empty($cad_usuario_id)) {
    $form->addField('<button type="button" class="btn btn-outline-primary my-1" data-bs-toggle="modal" data-bs-target="#changePass">Alterar Senha</button>');
} else {
    $form->addField(passField("Senha", NULL, NULL, true, true, "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$"));
    $form->addField(passField("Confirmar Senha", NULL, NULL, true, false, "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$"));
}

$form->addField(checkboxField("Ativo | Inativo", $f_status, "f_ativo"));
$form->addField(submitBtn("Salvar"));
$form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

if (!empty($cad_usuario_id)) {
    $changePass = new Form("usuariosCadSave.php");
    $changePass->addField(hiddenField($cad_usuario_id, "cad_usuario_id", "usuario_id"));
    $changePass->addField(passField("Senha", NULL, NULL, true, true, "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$"));
    $changePass->addField(passField("Confirmar Senha", NULL, NULL, true, false, "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$"));
    $changePass->addField(submitBtn("Salvar"));

    $modal = '
    <div class="modal fade" id="changePass" tabindex="-1" aria-labelledby="changePassLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="changePassLabel">Digite sua nova senha!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ' . $changePass->writeHtml() . '
                </div>
            </div>
        </div>
    </div>
    ';
}

$template = new Template("Cadastro de Usuários");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addBreadcrumb("Listagem de Usuários", "usuariosList.php");
$template->addContent($form->writeHtml(), true);
$template->addJS('
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginImageEdit
  );
  
  FilePond.setOptions({
    labelIdle: `<i class="ph ph-camera"></i></br> Arraste e solte sua foto ou navegue <span class="filepond--label-action"></span>`,
    maxFileSize: "5MB",
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
    credits: false,
    server: {
      process: "./usuariosCadSave.php",
      revert: "/your-revert-endpoint",
      fetch: null,
    },
  });
  
  var pond = FilePond.create(document.querySelector(".filepond"));
  
  pond.on("fileuploaded", function (event) {
    // Get the uploaded file
    const file = event.file;
    console.log(file);
    // Read the file as base64
    file.read((error, base64Data) => {
      if (!error) {
        // base64Data contains the base64 representation of the uploaded image
        console.log(base64Data);
  
        // Now you can do something with the base64 data, like sending it to the server
        // using an AJAX request or setting it as the value of a hidden input field in a form
      } else {
        console.error(error);
      }
    });
  });
  
');
if (!empty($cad_usuario_id)) {
    $template->addContent($modal);
}
$template->writeHtml();
