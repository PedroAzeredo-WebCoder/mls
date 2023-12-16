<?php
require_once("./inc/common.php");
checkAccess("cargosList");

$pagination = new Pagination();

$table = new Table();
$table->cardHeader(btn("Novo", "cargosCad.php"));
$table->addHeader("Nome");
$table->addHeader("Permissões",             "text-center", "col-2", false);
$table->addHeader("Status",             "text-center", "col-1", false);
$table->addHeader("Ação",               "text-center", "col-1", false);

$query = new sqlQuery();
$query->addTable("cad_cargos");
$query->addcolumn("nome");
$query->addcolumn("(SELECT GROUP_CONCAT((SELECT nome FROM adm_menu WHERE id IN(cargos_has_permissoes.adm_menu_id)))FROM cargos_has_permissoes WHERE cad_cargo_id = cad_cargos.id) AS permissoes");
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

$query->addOrder("id", "DESC");

$resultCount = $conn->query($query->getSQL())->rowCount();

$query->setLimit(PAGINATION, $pagination->startLimit());

$pagination->setSQL($query->getCount());

$table->setCount($resultCount);

if ($conn->query($query->getSQL()) && getDbValue($query->getCount()) != 0) {
    $item = array();
    foreach ($conn->query($query->getSQL()) as $row) {
        if ($row["status"] == 1) {
            $status = badge("Ativo", "success");
        } else {
            $status = badge("Inativo", "danger");
        }

        $permissoes = explode(",", $row["permissoes"]);
        $badges = array_map(function ($permissao) {
            return badge(trim($permissao), "primary");
        }, $permissoes);

        $table->addCol(btn($row['nome'], ["cargosCad.php", ["cad_cargo_id" => $row["id"]]], "btn-link ps-0 fw-normal edit"));
        $table->addCol(implode(" ", $badges), "text-end");
        $table->addCol($status, "text-center");

        if ($row["status"] != 1) {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["cargosCad.php", ["cad_cargo_id" => $row["id"]]], "transparent", "btn-sm btn-outline-danger mx-1 edit") . btn("<i class='fa-solid fa-trash'></i>", ["cargosCadSave.php", ["cad_cargo_id_delete" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        } else {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["cargosCad.php", ["cad_cargo_id" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        }
        $table->endRow();
    }
} else {
    $table->addCol("Nenhum registro encontrado!", "text-center", count($table->getHeaders()));
    $table->endRow();
}

$template = new Template("Listagem de Cargos");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addContent($table->writeHtml());
$template->addContent($pagination->writeHtml());
$template->writeHtml();
