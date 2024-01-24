<?php
require_once("./inc/common.php");
checkAccess("categoriasList");

$pagination = new Pagination();

$table = new Table();
$table->cardHeader(btn("Novo", "categoriasCad.php"));
$table->addHeader("Icone",     "text-center", "col-1", false);
$table->addHeader("Nome");
$table->addHeader("Status",     "text-center", "col-1", false);
$table->addHeader("Ação",       "text-center", "col-1", false);

$query = new sqlQuery();
$query->addTable("cad_categorias");
$query->addcolumn("icone");
$query->addcolumn("nome");
$query->addcolumn("status");
$query->addcolumn("id");

if (!empty($_COOKIE['filter_status'])) {
    $query->addWhere("status", "=", "'" . $_COOKIE['filter_status'] . "'");
} else {
    $f_searchTableStatus = getParam("f_searchTableStatus");
    if ($f_searchTableStatus || $f_searchTableStatus === "0") {
        $query->addWhere("status", "=", "'" . $f_searchTableStatus . "'");
    } else {
        $query->addWhere("status", "=", "'1'");
    }
}

$query->addOrder("nome", "ASC");

$resultCount = $conn->query($query->getSQL())->rowCount();

$query->setLimit(PAGINATION, $pagination->startLimit());

$pagination->setSQL($query->getCount());

$table->setCount($resultCount);

if ($conn->query($query->getSQL()) && getDbValue($query->getCount()) != 0) {
    foreach ($conn->query($query->getSQL()) as $row) {
        if ($row["status"] == 1) {
            $status = badge("Ativo", "success");
        } else {
            $status = badge("Inativo", "danger");
        }

        $table->addCol($row['icone'], "text-center");
        $table->addCol(btn($row['nome'], ["categoriasCad.php", ["cad_categoria_id" => $row["id"]]], "btn-link ps-0 fw-normal edit"));
        $table->addCol($status, "text-center");
        if ($row["status"] != 1) {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["categoriasCad.php", ["cad_categoria_id" => $row["id"]]], "transparent", "btn-sm btn-outline-danger mx-1 edit") . btn("<i class='fa-solid fa-trash'></i>", ["categoriasCadSave.php", ["cad_categoria_id_delete" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        } else {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["categoriasCad.php", ["cad_categoria_id" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        }
        $table->endRow();
    }
} else {
    $table->addCol("Nenhum registro encontrado!", "text-center", count($table->getHeaders()));
    $table->endRow();
}

$template = new Template("Listagem de Categorias");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addContent($table->writeHtml());
$template->addContent($pagination->writeHtml());
$template->writeHtml();
