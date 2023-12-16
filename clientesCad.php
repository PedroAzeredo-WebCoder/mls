<?php
require_once("./inc/common.php");
checkAccess("clientesList");

$e = getParam("e", true);
$cad_cliente_id = $e["cad_cliente_id"];

$f_status = "checked";

if (!empty($cad_cliente_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_usuarios");
    $query->addcolumn("id");
    $query->addcolumn("nome");
    $query->addcolumn("documento");
    $query->addcolumn("email");
    $query->addcolumn("celular");
    $query->addcolumn("dt_nascimento");
    $query->addcolumn("cep");
    $query->addcolumn("estado");
    $query->addcolumn("cidade");
    $query->addcolumn("bairro");
    $query->addcolumn("logradouro");
    $query->addcolumn("numero");
    $query->addcolumn("complemento");
    $query->addcolumn("parente_nome");
    $query->addcolumn("parente_parentesco");
    $query->addcolumn("parente_celular");
    $query->addcolumn("cad_origem_id");
    $query->addcolumn("(SELECT GROUP_CONCAT(cad_objetivo_id) FROM usuarios_has_objetivos WHERE cad_usuario_id = cad_usuarios.id) as objetivos");
    $query->addcolumn("(SELECT GROUP_CONCAT(cad_informacao_saude_id) FROM usuarios_has_informacoes_saude WHERE cad_usuario_id = cad_usuarios.id) as informacoes_saude");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_cliente_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_documento = $row["documento"];
        $f_email = $row["email"];
        $f_celular = $row["celular"];
        $f_dt_nascimento = $row["dt_nascimento"];
        $f_cep = $row["cep"];
        $f_estado = $row["estado"];
        $f_cidade = $row["cidade"];
        $f_bairro = $row["bairro"];
        $f_logradouro = $row["logradouro"];
        $f_numero = $row["numero"];
        $f_complemento = $row["complemento"];
        $f_parente_nome = $row["parente_nome"];
        $f_parente_parentesco = $row["parente_parentesco"];
        $f_parente_celular = $row["parente_celular"];
        $cad_origem_id = $row["cad_origem_id"];
        $f_objetivos = explode(",", $row["objetivos"]);
        $f_informacoes_saude = explode(",", $row["informacoes_saude"]);
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }
}

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
$form->addField(hiddenField($cad_cliente_id, "cad_cliente_id"));
$form->addField(fileField("Foto de Perfil", $f_imagem, "f_imagem"));
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
$form->addField(listField("Origem", $options_f_origens, $cad_origem_id, "cad_origem_id"));
$form->addField(listMultipleField("Objetivos", $options_f_objetivos, $f_objetivos));
$form->addField(listMultipleField("Informações de Saúde", $options_f_informacoesSaude, $f_informacoes_saude, "f_informacoes_saude", true));
$form->addField('<hr/>');
$form->addField('Em caso de emergência avisar quem?');
$form->addField(textField("Nome", $f_parente_nome, "f_parente_nome", true));
$form->addField(textField("Grau de Parentesco", $f_parente_parentesco, "f_parente_parentesco", true));
$form->addField(telField("Celular", $f_parente_celular, "f_parente_celular", true, "\(\d{2}\)\s?\d?(\d{4,5})-?\d{4}"));
$form->addField('<hr/>');

if (!empty($cad_cliente_id)) {
    $form->addField('<button type="button" class="btn btn-outline-primary my-1" data-bs-toggle="modal" data-bs-target="#changePass">Alterar Senha</button>');
} else {
    $form->addField(passField("Senha", NULL, NULL, true));
    $form->addField(passField("Confirmar Senha", NULL, NULL, true));
}
$form->addField(checkboxField("Ativo | Inativo", $f_status, "f_ativo"));
$form->addField(submitBtn("Salvar"));
$form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

if (!empty($cad_cliente_id)) {
    $changePass = new Form("clientesCadSave.php");
    $changePass->addField(hiddenField($cad_cliente_id, "cad_cliente_id"));
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

$template = new Template("Cadastro de Clientes");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addBreadcrumb("Listagem de Clientes", "clientesList.php");
$template->addContent($form->writeHtml(), true);
if (!empty($cad_cliente_id)) {
    $template->addContent($modal);
}
$template->writeHtml();
