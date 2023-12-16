<?php
require_once("./inc/common.php");
checkAccess();

$e = getParam("e", true);
$cad_usuario_id = $e["cad_usuario_id"];

$f_status = "checked";

$diasSemana =  array();
for ($i = 1; $i <= 6; $i++) {
    $diasSemana[] = br_DiaSemana($i);
}

if (!empty($cad_usuario_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_usuarios");
    $query->addcolumn("id");
    $query->addcolumn("imagem");
    $query->addcolumn("nome");
    $query->addcolumn("documento");
    $query->addcolumn("email");
    $query->addcolumn("celular");
    $query->addcolumn("cad_cargo_id");
    $query->addcolumn("dt_nascimento");
    $query->addcolumn("cep");
    $query->addcolumn("estado");
    $query->addcolumn("cidade");
    $query->addcolumn("bairro");
    $query->addcolumn("logradouro");
    $query->addcolumn("numero");
    $query->addcolumn("complemento");
    $query->addcolumn("status");
    $query->addcolumn("tipo");
    $query->addcolumn("parente_nome");
    $query->addcolumn("parente_parentesco");
    $query->addcolumn("parente_celular");
    $query->addcolumn("cad_origem_id");
    $query->addcolumn("(SELECT GROUP_CONCAT(cad_objetivo_id) FROM usuarios_has_objetivos WHERE cad_usuario_id = cad_usuarios.id) as objetivos");
    $query->addcolumn("(SELECT GROUP_CONCAT((SELECT nome FROM cad_objetivos WHERE id IN(usuarios_has_objetivos.cad_objetivo_id)))FROM usuarios_has_objetivos WHERE cad_usuario_id = cad_usuarios.id) AS objetivos_bedges");
    $query->addcolumn("(SELECT GROUP_CONCAT(cad_informacao_saude_id) FROM usuarios_has_informacoes_saude WHERE cad_usuario_id = cad_usuarios.id) as informacoes_saude");
    $query->addcolumn("(SELECT GROUP_CONCAT(cad_servico_id) FROM usuarios_has_especialidades WHERE cad_usuario_id = cad_usuarios.id) as especialidades");
    $query->addcolumn("(SELECT GROUP_CONCAT((SELECT nome FROM cad_servicos WHERE id IN(usuarios_has_especialidades.cad_servico_id)))FROM usuarios_has_especialidades WHERE cad_usuario_id = cad_usuarios.id) AS especialidades_bedges");
    $query->addcolumn("cad_local_id");
    for ($i = 1; $i <= 6; $i++) {
        $query->addcolumn("horario_" . strtolower(slug(br_DiaSemana($i))));
    }
    $query->addWhere("id", "=", $cad_usuario_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_documento = $row["documento"];
        $f_email = $row["email"];
        $f_celular = $row["celular"];
        $cad_cargo_id = $row["cad_cargo_id"];
        $cad_local_id = $row["cad_local_id"];
        $f_dt_nascimento = $row["dt_nascimento"];
        $f_cep = $row["cep"];
        $f_estado = $row["estado"];
        $f_cidade = $row["cidade"];
        $f_bairro = $row["bairro"];
        $f_logradouro = $row["logradouro"];
        $f_numero = $row["numero"];
        $f_complemento = $row["complemento"];
        $f_imagem = $row["imagem"];
        $f_tipo = $row["tipo"];
        $f_parente_nome = $row["parente_nome"];
        $f_parente_parentesco = $row["parente_parentesco"];
        $f_parente_celular = $row["parente_celular"];
        $cad_origem_id = $row["cad_origem_id"];
        $f_objetivos = explode(",", $row["objetivos"]);
        $f_objetivos_bedges = explode(",", $row["objetivos_bedges"]);
        $f_informacoes_saude = explode(",", $row["informacoes_saude"]);
        $f_especialidades = explode(",", $row["especialidades"]);
        $f_especialidades_bedges = explode(",", $row["especialidades_bedges"]);
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }

        $horarios_semana = array();

        for ($i = 1; $i <= 6; $i++) {
            $campo_horario = "horario_" . strtolower(slug(br_DiaSemana($i)));

            if (!empty($row[$campo_horario])) {
                $horario = explode("|", $row[$campo_horario]);
                $horarios_semana[$i] = $horario;
            }
        }

        $badges = array();
        $text = '';
        if ($f_tipo == '1') {
            if (count($f_objetivos_bedges) > 1) {
                $badges = array_map(function ($objetivo) {
                    return badge(trim($objetivo), "primary");
                }, $f_objetivos_bedges);
                $text = 'Objetivos';
            }
        } else {
            if (count($f_especialidades_bedges) > 1) {
                $badges = array_map(function ($especialidade) {
                    return badge(trim($especialidade), "primary");
                }, $f_especialidades_bedges);
                $text = 'Especialidades';
            }
        }

        $info = '
            <h3>' . $text . '</h3>
            <div class="d-flex gap-1">
                ' . implode("", $badges) . '
            </div>
        ';
    }
}

if ($f_tipo == '1') {

    $origens = new sqlQuery();
    $origens->addTable("cad_origens");
    $origens->addcolumn("id");
    $origens->addcolumn("nome");
    $origens->addWhere("status", "=", "1");

    if ($conn->query($origens->getSQL()) && getDbValue($origens->getCount()) != 0) {
        foreach ($conn->query($origens->getSQL()) as $row) {
            $options_f_origens[] = array("id" => $row["id"], "name" => $row["nome"]);
        }
    } else {
        $options_f_origens[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $objetivos = new sqlQuery();
    $objetivos->addTable("cad_objetivos");
    $objetivos->addcolumn("id");
    $objetivos->addcolumn("nome");
    $objetivos->addWhere("status", "=", "1");

    if ($conn->query($objetivos->getSQL()) && getDbValue($objetivos->getCount()) != 0) {
        foreach ($conn->query($objetivos->getSQL()) as $row) {
            $options_f_objetivos[] = array("id" => $row["id"], "name" => $row["nome"]);
        }
    } else {
        $options_f_objetivos[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $informacoesSaude = new sqlQuery();
    $informacoesSaude->addTable("cad_informacoes_saude");
    $informacoesSaude->addcolumn("id");
    $informacoesSaude->addcolumn("nome");
    $informacoesSaude->addWhere("status", "=", "1");

    if ($conn->query($informacoesSaude->getSQL()) && getDbValue($informacoesSaude->getCount()) != 0) {
        foreach ($conn->query($informacoesSaude->getSQL()) as $row) {
            $options_f_informacoesSaude[] = array("id" => $row["id"], "name" => $row["nome"]);
        }
    } else {
        $options_f_informacoesSaude[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $form = new Form("clientesCadSave.php");
    $form->setUpload(true);
    $form->addField(hiddenField($cad_usuario_id, "cad_cliente_id"));
} else {

    $locais = new sqlQuery();
    $locais->addTable("cad_locais");
    $locais->addcolumn("id");
    $locais->addcolumn("nome");
    $locais->addWhere("status", "=", "1");

    if ($conn->query($locais->getSQL()) && getDbValue($locais->getCount()) != 0) {
        foreach ($conn->query($locais->getSQL()) as $row) {
            $options_f_locais[] = array("id" => $row["id"], "name" => $row["nome"]);
        }
    } else {
        $options_f_locais[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $especialidades = new sqlQuery();
    $especialidades->addTable("cad_servicos");
    $especialidades->addcolumn("id");
    $especialidades->addcolumn("nome");
    $especialidades->addWhere("status", "=", "1");

    if ($conn->query($especialidades->getSQL()) && getDbValue($especialidades->getCount()) != 0) {
        foreach ($conn->query($especialidades->getSQL()) as $row) {
            $options_f_especialidades[] = array("id" => $row["id"], "name" => $row["nome"]);
        }
    } else {
        $options_f_especialidades[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $diasHorarios = '
    <fieldset class="mb-1" id="horarios">
        <label class="form-label">Dias e Horário de Disponibilidade</label>
        <div class="row g-1">
    ';

    foreach ($diasSemana as $i => $dia) {
        $horario = isset($horarios_semana[$i + 1]) ? $horarios_semana[$i + 1] : ['', ''];

        $diasHorarios .= '
        <div class="col-lg-6">
            <div class="input-group">
                <span class="input-group-text bg-light">' . $dia . '</span>
                <input type="time" class="form-control horario-input" name="' . strtolower(slug($dia)) . '_inicial" value="' . $horario[0] . '" placeholder="09:00">
                <span class="input-group-text bg-light">às</span>
                <input type="time" class="form-control horario-input" name="' . strtolower(slug($dia)) . '_final" value="' . $horario[1] . '" placeholder="18:00">
            </div>
        </div>';
    }

    $diasHorarios .= '
        </div>
    </fieldset>';

    $form = new Form("usuariosCadSave.php");
    $form->setUpload(true);
    $form->addField(hiddenField($cad_usuario_id, "cad_usuario_id"));
}

$form->addField(hiddenField('on', "f_ativo"));
$form->addField(hiddenField(getUserInfo("cad_cargo_id"), "f_cargo"));
$form->addField(fileField("Foto de Perfil", NULL, "f_imagem"));
$form->addField(textField("Nome", $f_nome, NUll, true));
$form->addField(textField("CPF/CNPJ", $f_documento, "f_documento", true, "\d{3}\.\d{3}\.\d{3}-\d{2}|\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}"));
$form->addField(emailField("E-mail", $f_email, NUll, true, "^(?=.{1,256})(?=.{1,64}@)[^\s@]+@[^\s@]+\.[^\s@]{2,}$", NULL, "text-lowercase"));
$form->addField(telField("Celular", $f_celular, NUll, false, "\(\d{2}\)\s?\d?(\d{4,5})-?\d{4}"));
$form->addField(dateField("Dt Nascimento", $f_dt_nascimento));
$form->addField(textField("CEP", $f_cep, NULL, true, "\d{5}-\d{3}"));
$form->addField(textField("Estado", $f_estado, "f_uf", true, "[A-Z]{2}"));
$form->addField(textField("Cidade", $f_cidade, "f_localidade", true));
$form->addField(textField("Bairro", $f_bairro, NULL, true));
$form->addField(textField("Logradouro", $f_logradouro, NULL, true));
$form->addField(textField("Número", $f_numero, NULL, true));
$form->addField(textField("Complemento", $f_complemento));

if ($f_tipo == '1') {
    $form->addField(listField("Origem", $options_f_origens, $cad_origem_id, "cad_origem_id"));
    $form->addField(listMultipleField("Objetivos", $options_f_objetivos, $f_objetivos));
    $form->addField(listMultipleField("Informações de Saúde", $options_f_informacoesSaude, $f_informacoes_saude, "f_informacoes_saude", true));
    $form->addField('<hr/>');
    $form->addField('Em caso de emergência avisar quem?');
    $form->addField(textField("Nome", $f_parente_nome, "f_parente_nome", true));
    $form->addField(textField("Grau de Parentesco", $f_parente_parentesco, "f_parente_parentesco", true));
    $form->addField(telField("Celular", $f_parente_celular, "f_parente_celular", true, "\(\d{2}\)\s?\d?(\d{4,5})-?\d{4}"));
    $form->addField('<hr/>');
} else {
    $form->addField(listField("Local", $options_f_locais, $cad_local_id));
    $form->addField($diasHorarios);
    $form->addField(listMultipleField("Especialidades", $options_f_especialidades, $f_especialidades, "f_especialidades"));
}
$form->addField('<button type="button" class="btn btn-outline-primary my-1 d-block" data-bs-toggle="modal" data-bs-target="#changePass">Alterar Senha</button>');
$form->addField(submitBtn("Salvar"));
$form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

if (!empty($cad_usuario_id)) {
    $changePass = new Form("usuariosCadSave.php");
    $changePass->addField(hiddenField($cad_usuario_id, "cad_usuario_id", "id"));
    $changePass->addField(passField("Senha", NULL, NULL, true));
    $changePass->addField(passField("Confirmar Senha", NULL, NULL, true));
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

$imagem = '<svg viewBox="0 0 212 212" width="100%"><path fill="#DFE5E7" class="background" d="M106.251,0.5C164.653,0.5,212,47.846,212,106.25S164.653,212,106.25,212C47.846,212,0.5,164.654,0.5,106.25 S47.846,0.5,106.251,0.5z"></path><g><path fill="#FFFFFF" class="primary" d="M173.561,171.615c-0.601-0.915-1.287-1.907-2.065-2.955c-0.777-1.049-1.645-2.155-2.608-3.299 c-0.964-1.144-2.024-2.326-3.184-3.527c-1.741-1.802-3.71-3.646-5.924-5.47c-2.952-2.431-6.339-4.824-10.204-7.026 c-1.877-1.07-3.873-2.092-5.98-3.055c-0.062-0.028-0.118-0.059-0.18-0.087c-9.792-4.44-22.106-7.529-37.416-7.529 s-27.624,3.089-37.416,7.529c-0.338,0.153-0.653,0.318-0.985,0.474c-1.431,0.674-2.806,1.376-4.128,2.101 c-0.716,0.393-1.417,0.792-2.101,1.197c-3.421,2.027-6.475,4.191-9.15,6.395c-2.213,1.823-4.182,3.668-5.924,5.47 c-1.161,1.201-2.22,2.384-3.184,3.527c-0.964,1.144-1.832,2.25-2.609,3.299c-0.778,1.049-1.464,2.04-2.065,2.955 c-0.557,0.848-1.033,1.622-1.447,2.324c-0.033,0.056-0.073,0.119-0.104,0.174c-0.435,0.744-0.79,1.392-1.07,1.926 c-0.559,1.068-0.818,1.678-0.818,1.678v0.398c18.285,17.927,43.322,28.985,70.945,28.985c27.678,0,52.761-11.103,71.055-29.095 v-0.289c0,0-0.619-1.45-1.992-3.778C174.594,173.238,174.117,172.463,173.561,171.615z"></path><path fill="#FFFFFF" class="primary" d="M106.002,125.5c2.645,0,5.212-0.253,7.68-0.737c1.234-0.242,2.443-0.542,3.624-0.896 c1.772-0.532,3.482-1.188,5.12-1.958c2.184-1.027,4.242-2.258,6.15-3.67c2.863-2.119,5.39-4.646,7.509-7.509 c0.706-0.954,1.367-1.945,1.98-2.971c0.919-1.539,1.729-3.155,2.422-4.84c0.462-1.123,0.872-2.277,1.226-3.458 c0.177-0.591,0.341-1.188,0.49-1.792c0.299-1.208,0.542-2.443,0.725-3.701c0.275-1.887,0.417-3.827,0.417-5.811 c0-1.984-0.142-3.925-0.417-5.811c-0.184-1.258-0.426-2.493-0.725-3.701c-0.15-0.604-0.313-1.202-0.49-1.793 c-0.354-1.181-0.764-2.335-1.226-3.458c-0.693-1.685-1.504-3.301-2.422-4.84c-0.613-1.026-1.274-2.017-1.98-2.971 c-2.119-2.863-4.646-5.39-7.509-7.509c-1.909-1.412-3.966-2.643-6.15-3.67c-1.638-0.77-3.348-1.426-5.12-1.958 c-1.181-0.355-2.39-0.655-3.624-0.896c-2.468-0.484-5.035-0.737-7.68-0.737c-21.162,0-37.345,16.183-37.345,37.345 C68.657,109.317,84.84,125.5,106.002,125.5z"></path></g></svg>';
if (!empty($f_imagem)) {
    $imagem = '<img src="data:image/png;base64,' . $f_imagem . '" class="img-fluid rounded-circle ratio ratio-1x1 avatar-lg">';
}

$desativar = btnLink(["usuariosCadSave.php", ["cad_usuario_id_status" => getUserInfo("id")]]);
if ($f_tipo == '1') {
    $desativar = btnLink(["clientesCadSave.php", ["cad_cliente_id_status" => getUserInfo("id")]]);
}

$html = '
<div class="mb-3">
    <div class="row g-0 align-items-center justify-content-md-start justify-content-center">
        <div class="col-md-2 col-8 px-3">
            ' . $imagem . '
        </div>
        <div class="col-md-7">
            <div class="card-body">
                <h4 class="card-title fw-bolder text-lg-start text-center">' . getUserInfo("nome ") . ' <i class="ph ph-seal-check"></i></h4>
                <div class="row">
                    <div class="col-lg-5">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item ps-0">
                                <i class="ph ph-map-pin"></i>
                                ' . getUserInfo("cidade") . ' / ' . getUserInfo("estado") . '
                            </li>
                            <li class="list-group-item ps-0">
                                <i class="ph ph-phone"></i>
                                ' . getUserInfo("celular") . '
                            </li>
                            <li class="list-group-item ps-0">
                                <i class="ph ph-envelope-simple"></i>
                                ' . getUserInfo("email") . '
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            ' . $info . '
        </div>
    </div>
    <hr />
    <h4>Editar dados</h4>
    ' . $form->writeHtml() . '
    <div class="d-flex justify-content-end">
        <a href="' . $desativar . '" class="btn btn-link">Desativar conta</a>
    </div>
</div>
';

$template = new Template("Meu Perfil");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addContent($html, true);
if (!empty($cad_usuario_id)) {
    $template->addContent($modal);
}
$template->writeHtml();
