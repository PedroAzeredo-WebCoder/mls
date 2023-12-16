<?php
require_once("./inc/common.php");
checkAccess("estoqueList");

$e = getParam("e", true);
$cad_estoque_id = $e["cad_estoque_id"];

$f_status = "checked";

if (!empty($cad_estoque_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_estoque");
    $query->addcolumn("id");
    $query->addcolumn("nome");
    $query->addcolumn("valor");
    $query->addcolumn("quantidade");
    $query->addcolumn("cad_categoria_id");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_estoque_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_valor = number_format($row["valor"], 2, ",", ".");
        $f_quantidade  = $row["quantidade"];
        $cad_categoria_id = $row["cad_categoria_id"];
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }
}

$categorias = new sqlQuery();
$categorias->addTable("cad_categorias");
$categorias->addcolumn("id");
$categorias->addcolumn("nome");
$categorias->addWhere("status", "=", "1");

if ($conn->query($categorias->getSQL()) && getDbValue($categorias->getCount()) != 0) {
    foreach ($conn->query($categorias->getSQL()) as $row) {
        $options_f_categorias[] = array("id" => $row["id"], "name" => $row["nome"]);
    }
} else {
    $options_f_categorias[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
}

$form = new Form("estoqueCadSave.php");
$form->setUpload(true);
$form->addField(hiddenField($cad_estoque_id, "cad_estoque_id"));
$form->addField(textField("Nome", $f_nome, NUll, true));
$form->addField(textField("Valor", 'R$ ' . $f_valor, NULL, true));
$form->addField(numberField("Quantidade", $f_quantidade,NULL,true, 1));
$form->addField(listField("Categoria", $options_f_categorias, $cad_categoria_id, NULL, true));
$form->addField(checkboxField("Ativo | Inativo", $f_status, "f_ativo"));
$form->addField(submitBtn("Salvar"));
$form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

$template = new Template("Cadastro de Itens");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addBreadcrumb("Listagem de Itens no Estoque", "estoqueList.php");
$template->addContent($form->writeHtml(), true);
$template->writeHtml();
