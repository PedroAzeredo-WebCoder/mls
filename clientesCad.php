<?php
require_once("./inc/common.php");
checkAccess("clientesList");

$e = getParam("e", true);
$cad_cliente_id = $e["cad_cliente_id"];

$f_status = "checked";

if (!empty($cad_cliente_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_clientes");
    $query->addcolumn("id");
    $query->addcolumn("nome");
    $query->addcolumn("email");
    $query->addcolumn("celular");
    $query->addcolumn("cep");
    $query->addcolumn("estado");
    $query->addcolumn("cidade");
    $query->addcolumn("bairro");
    $query->addcolumn("logradouro");
    $query->addcolumn("numero");
    $query->addcolumn("complemento");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_cliente_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_email = $row["email"];
        $f_celular = $row["celular"];
        $f_cep = $row["cep"];
        $f_estado = $row["estado"];
        $f_cidade = $row["cidade"];
        $f_bairro = $row["bairro"];
        $f_logradouro = $row["logradouro"];
        $f_numero = $row["numero"];
        $f_complemento = $row["complemento"];
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }
}

$form = new Form("clientesCadSave.php");
$form->setUpload(true);
$form->addField(hiddenField($cad_cliente_id, "cad_cliente_id"));
$form->addField(fileField("Foto de Perfil", $f_imagem, "f_imagem"));
$form->addField(textField("Nome", $f_nome, NUll, true));
$form->addField(emailField("E-mail", $f_email, NUll, false, "^(?=.{1,256})(?=.{1,64}@)[^\s@]+@[^\s@]+\.[^\s@]{2,}$", NULL, "text-lowercase"));
$form->addField(telField("Celular", $f_celular, NUll, false, "\(\d{2}\)\s?\d?(\d{4,5})-?\d{4}"));
$form->addField(textField("CEP", $f_cep, NULL, true, "\d{5}-\d{3}"));
$form->addField(textField("Estado", $f_estado, "f_uf", true, "[A-Z]{2}"));
$form->addField(textField("Cidade", $f_cidade, "f_localidade", true));
$form->addField(textField("Bairro", $f_bairro, NULL, true));
$form->addField(textField("Logradouro", $f_logradouro, NULL, true));
$form->addField(textField("Número", $f_numero, NULL, true));
$form->addField(textField("Complemento", $f_complemento));

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
