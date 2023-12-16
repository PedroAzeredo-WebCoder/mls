<?php
require_once("./inc/common.php");
checkAccess("cargosList");

$e = getParam("e", true);
$cad_cargo_id = $e["cad_cargo_id"];

$f_status = "checked";
$f_permissoes = array();

if (!empty($cad_cargo_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_cargos");
    $query->addcolumn("id");
    $query->addcolumn("nome");
    $query->addcolumn("(SELECT GROUP_CONCAT(adm_menu_id) FROM cargos_has_permissoes WHERE cad_cargo_id = cad_cargos.id) as permissoes");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_cargo_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $f_nome = $row["nome"];
        $f_permissoes = explode(",", $row["permissoes"]);
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }
}

$permissoes = new sqlQuery();
$permissoes->addTable("adm_menu");
$permissoes->addcolumn("id");
$permissoes->addcolumn("nome");
$permissoes->addWhere("status", "=", 1);
$permissoes->addWhere("adm_menu_id", "IS NULL");

foreach ($conn->query($permissoes->getSQL()) as $row_permissoes) {
    $options_f_permissoes[] = array("id" => $row_permissoes["id"], "name" => $row_permissoes["nome"]);

    $permissoesSubItens = new sqlQuery();
    $permissoesSubItens->addTable("adm_menu");
    $permissoesSubItens->addcolumn("id");
    $permissoesSubItens->addcolumn("nome");
    $permissoesSubItens->addWhere("status", "=", 1);
    $permissoesSubItens->addWhere("adm_menu_id", "=", $row_permissoes["id"]);

    foreach ($conn->query($permissoesSubItens->getSQL()) as $row_permissoes) {
        $selected = "";
        if (in_array($row_permissoes["id"], $f_permissoes)) {
            $selected = "selected";
        }
        $options_f_permissoes[] = array("id" => $row_permissoes["id"], "name" => "-- " . $row_permissoes["nome"]);
    }
}

$form = new Form("cargosCadSave.php");
$form->addField(hiddenField($cad_cargo_id, "cad_cargo_id"));
$form->addField(textField("Nome", $f_nome, NUll, true));
$form->addField(listMultipleField("Permissões", $options_f_permissoes, $f_permissoes, NULL, true));
$form->addField(checkboxField("Ativo | Inativo", $f_status, "f_ativo"));
$form->addField(submitBtn("Salvar"));
$form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

$template = new Template("Cadastro de Cargos");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addBreadcrumb("Listagem de Cargos", "cargosList.php");
$template->addContent($form->writeHtml(), true);
$template->writeHtml();
