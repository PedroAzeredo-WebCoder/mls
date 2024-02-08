<?php
require_once("./inc/common.php");
checkAccess("pedidosList");

$pagination = new Pagination();

$table = new Table();
$table->cardHeader(btn("Novo", "pedidosCad.php"));
$table->addHeader("Cliente");
$table->addHeader("Produto",             "text-center", "col-3", false);
$table->addHeader("Valor",             "text-center", "col-1", false);
$table->addHeader("Data",             "text-center", "col-1", false);
$table->addHeader("Status Pagamento",     "text-center", "col-1", false);
$table->addHeader("Ação",       "text-center", "col-1", false);

$query = new sqlQuery();
$query->addTable("cad_pedidos");
$query->addJoin("pedidos_has_produtos", "cad_pedidos.id = pedidos_has_produtos.cad_pedido_id", "LEFT");
$query->addcolumn("(SELECT nome FROM cad_clientes WHERE id = cad_cliente_id) AS nome");
$query->addcolumn("(SELECT celular FROM cad_clientes WHERE id = cad_cliente_id) AS celular");
$query->addcolumn("(SELECT logradouro FROM cad_clientes WHERE id = cad_cliente_id) AS logradouro");
$query->addcolumn("(SELECT numero FROM cad_clientes WHERE id = cad_cliente_id) AS numero");
$query->addcolumn("(SELECT complemento FROM cad_clientes WHERE id = cad_cliente_id) AS complemento");
$query->addcolumn("GROUP_CONCAT((SELECT nome FROM cad_estoque WHERE id = pedidos_has_produtos.cad_estoque_id ) SEPARATOR '|') AS produtos");
$query->addcolumn("GROUP_CONCAT(pedidos_has_produtos.quantidade SEPARATOR '|') AS quantidades");
$query->addcolumn("cad_pedidos.valor AS valor");
$query->addcolumn("DATE_FORMAT(cad_pedidos.dt_create, '%d/%m/%Y') AS data");
$query->addcolumn("cad_pedidos.status");
$query->addcolumn("cad_pedidos.id");

$newTableStatus = array(
    array("id" => "3", "name" => "Todos"),
    array("id" => "1", "name" => "Pago"),
    array("id" => "0", "name" => "Pendente")
);

$table->setTableStatus($newTableStatus);

//$filterStatus = !empty($_COOKIE['filter_status']) ? $_COOKIE['filter_status'] : null;
$f_searchTableStatus = getParam("f_searchTableStatus");
$defaultStatus = "3";

if ($f_searchTableStatus != $defaultStatus && ($f_searchTableStatus || $f_searchTableStatus === "0")) {
    $query->addWhere("cad_pedidos.status", "=", "'" . $f_searchTableStatus . "'");
} else {
    $query->addWhere("cad_pedidos.status", "!=", "'" . $defaultStatus . "'");
}

$query->addGroupBy("cad_pedidos.id");
$query->addOrder("cad_pedidos.dt_create", "DESC");


$resultCount = $conn->query($query->getSQL())->rowCount();

$query->setLimit(PAGINATION, $pagination->startLimit());

$pagination->setSQL($query->getCount());

$table->setCount($resultCount);

if ($conn->query($query->getSQL()) && getDbValue($query->getCount()) != 0) {

    foreach ($conn->query($query->getSQL()) as $row) {
        if ($row["status"] == 1) {
            $status = badge("Pago", "success");
        } else {
            $status = badge("Pendente", "danger");
        }

        $cliente = '
            <div>
                <p><strong>Nome:</strong> ' . $row['nome'] . ' | ' . $row['celular'] . ' <br/></p>
                <p><strong>Endereço:</strong> ' . $row['logradouro'] . ', ' . $row['numero'] . ' - ' . $row['complemento'] . '</p>
            </div>
        ';

        $produtos = explode("|", $row["produtos"]);
        $quantidades = explode("|", $row["quantidades"]);
        
        $htmlContent = '';
        foreach ($produtos as $index => $produto) {
            $quantidade = isset($quantidades[$index]) ? $quantidades[$index] : '';
            $htmlContent .= '<div><p>' . $produto . ' - ' . $quantidade . '<br /></p></div>';
        }
        
        $table->addCol(btn($cliente, ["pedidosCad.php", ["cad_pedido_id" => $row["id"]]], "btn-link ps-0 fw-normal text-start edit"));
        $table->addCol($htmlContent, "text-center");
        $table->addCol("R$ " . number_format($row['valor'], 2, ",", "."), "text-end");
        $table->addCol($row["data"], "text-center");
        $table->addCol($status, "text-center");
        $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["pedidosCad.php", ["cad_pedido_id" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        $table->endRow();
        
    }
} else {
    $table->addCol("Nenhum registro encontrado!", "text-center", count($table->getHeaders()));
    $table->endRow();
}

$template = new Template("Listagem de Itens no Pedidos");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addContent($table->writeHtml());
$template->addContent($pagination->writeHtml());
$template->writeHtml();
