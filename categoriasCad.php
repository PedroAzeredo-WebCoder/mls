<?php
require_once("./inc/common.php");
checkAccess("categoriasList");

$e = getParam("e", true);
$cad_categoria_id = $e["cad_categoria_id"];

$f_status = "checked";

if (!empty($cad_categoria_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_categorias");
    $query->addcolumn("id");
    $query->addcolumn("nome");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_categoria_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }
}

$form = new Form("categoriasCadSave.php");
$form->addField(hiddenField($cad_categoria_id, "cad_categoria_id"));
$form->addField(textField("Nome", $f_nome, NUll, true));
$form->addField(checkboxField("Ativo | Inativo", $f_status, "f_ativo"));
$form->addField(submitBtn("Salvar"));
$form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

$template = new Template("Cadastro de Categorias");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addBreadcrumb("Listagem de Categorias", "categoriasList.php");
$template->addContent($form->writeHtml(), true);
$template->writeHtml();
