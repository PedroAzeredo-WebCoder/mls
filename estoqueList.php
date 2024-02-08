<?php
require_once("./inc/common.php");
checkAccess("estoqueList");

$pagination = new Pagination();

$table = new Table();
$table->cardHeader(btn("Novo", "estoqueCad.php"));
$table->addHeader("Quantidade",             "text-center", "col-1", false);
$table->addHeader("Nome");
$table->addHeader("Valor",             "text-center", "col-2", false);
$table->addHeader("Valor Cobrado",             "text-center", "col-2", false);
$table->addHeader("Categoria",             "text-center", "col-3", false);
$table->addHeader("Status",     "text-center", "col-1", false);
$table->addHeader("Ação",       "text-center", "col-1", false);

$query = new sqlQuery();
$query->addTable("cad_estoque");
$query->addcolumn("quantidade");
$query->addcolumn("nome");
$query->addcolumn("valor");
$query->addcolumn("valor_cobrado");
$query->addcolumn("(SELECT nome FROM cad_categorias WHERE id = cad_categoria_id) AS cad_categoria_id");
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

$query->addOrder("cad_categoria_id", "ASC");
$query->addOrder("valor_cobrado", "ASC");

$resultCount = $conn->query($query->getSQL())->rowCount();

$query->setLimit(PAGINATION, $pagination->startLimit());

$pagination->setSQL($query->getCount());

$table->setCount($resultCount);

$mensagem = '';
$modal = '';

if ($conn->query($query->getSQL()) && getDbValue($query->getCount()) != 0) {

    foreach ($conn->query($query->getSQL()) as $row) {
        if ($row["status"] == 1) {
            $status = badge("Ativo", "success");
        } else {
            $status = badge("Inativo", "danger");
        }

        $table->addCol($row["quantidade"], "text-center");
        $table->addCol(btn($row['nome'], ["estoqueCad.php", ["cad_estoque_id" => $row["id"]]], "btn-link ps-0 fw-normal edit"));
        $table->addCol("R$ " . number_format($row["valor"], 2, ",", "."), "text-end");
        $table->addCol("R$ " . number_format($row["valor_cobrado"], 2, ",", "."), "text-end");
        $table->addCol(badge($row['cad_categoria_id'], "primary"), "text-center");
        $table->addCol($status, "text-center");
        if ($row["status"] != 1) {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["estoqueCad.php", ["cad_estoque_id" => $row["id"]]], "transparent", "btn-sm btn-outline-danger mx-1 edit") . btn("<i class='fa-solid fa-trash'></i>", ["estoqueCadSave.php", ["cad_estoque_id_delete" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        } else {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["estoqueCad.php", ["cad_estoque_id" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        }
        $table->endRow();
    }

    $despesa = getDbValue('SELECT SUM(valor * quantidade) FROM cad_estoque');
    $lucro = $receita - $despesa;

    $content = '
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <div class="alert alert-danger rounded d-flex align-items-center justify-content-center p-1">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col-10">
                            <h5 class="card-title">Despesas</h5>
                            <p class="card-text fs-2 fw-bolder text-dark">R$ ' . number_format($despesa, 2, ",", ".") . '
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <div class="alert alert-primary rounded d-flex align-items-center justify-content-center p-1">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col-10">
                            <h5 class="card-title">Receitas</h5>
                            <p class="card-text fs-2 fw-bolder text-dark">R$ ' . number_format($receita, 2, ",", ".") . '
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <div class="alert alert-success rounded d-flex align-items-center justify-content-center p-1">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col-10">
                            <h5 class="card-title">Lucros</h5>
                            <p class="card-text fs-2 fw-bolder text-dark">R$ ' . number_format($lucro, 2, ",", ".") . '
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ';

    $mensagem = '
    <div class="card">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h3 class="card-title m-0 fw-bolder"><i class="fa-regular fa-message"></i> Gerar mensagem de divulgação</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                Gerar
            </button>
        </div>
    </div>
    ';

    $resultados = new sqlQuery();
    $resultados->addTable("cad_estoque e");
    $resultados->addJoin("cad_categorias c", "e.cad_categoria_id = c.id");
    $resultados->addColumn("c.nome AS categoria");
    $resultados->addColumn("c.icone AS emoji");
    $resultados->addColumn("e.nome AS produto");
    $resultados->addColumn("e.valor");
    $resultados->addColumn("e.quantidade");
    $resultados->addWhere("e.status", "=", "'1'");
    $resultados->addOrder("c.id, e.valor");

    $formatado = [];

    if ($conn->query($resultados->getSQL()) && getDbValue($resultados->getCount()) != 0) {
        foreach ($conn->query($resultados->getSQL()) as $row) {
            $categoria = $row['categoria'];
            $produtoNome = $row['produto'];
            $valorFinalFormatted = number_format(arredondaValor($row['valor']), 2, ",", ".");
            $emoji = $row['emoji'];

            $produto_valor = $emoji . ' ' . $produtoNome . ' - *R$ ' . $valorFinalFormatted . '*';

            if (!isset($formatado[$categoria])) {
                $formatado[$categoria] = [];
            }

            $formatado[$categoria][] = $produto_valor;
        }
    }

    $texto = '
*🌙 Seja bem-vindo à ML´s Conveniência de Bebidas! 🌙*

Serviço de entrega de bebidas na porta de sua casa!
_____________________________________________
';

    foreach ($formatado as $categoria => $produtos) {
        $texto .= "\n*" . $categoria . "*\n";

        foreach ($produtos as $produto) {
            $texto .= "\n &nbsp;" . $produto . "\n";
        }
    }

    $texto .= '
_______________________________________________

🧊 Todas as bebidas entregues *geladas!*

🚛 Entregamos durante as *madrugadas de domingo a domingo!*

🏠 *Sem valor mínimo de pedido e sem taxa de entrega nos condomínios Salomoni!*

💸 Pagamento somente em Pix ou com o valor exato do pedido.

🚫 Não trabalhamos com troco!

*Chave Pix:* 04695293005

Faça seu pedido agora e aproveite a noite com as *melhores bebidas!* 🌙✨

*Entre em contato em:*

https://wa.me/5551994442101
ou
https://wa.me/5551995534873

Agora você pode fazer parte do nosso grupo de vendas acessando aqui 

👉 Clique aqui para entrar no grupo: https://chat.whatsapp.com/K107qqNkf4zGkiu6V0yDSp
';


    $modal = '
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Visualização da Mensagem</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ' . editorAreaField("Mensagem", $texto) . '
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary copy"><i class="fa-solid fa-copy"></i> Copiar</button>
                    </div>
                </div>
            </div>
        </div>
    ';
} else {
    $table->addCol("Nenhum registro encontrado!", "text-center", count($table->getHeaders()));
    $table->endRow();
}

$template = new Template("Listagem de Itens no Estoque");
$template->addBreadcrumb("Dashboard", "index.php");
//$template->addContent($content);
$template->addContent($table->writeHtml());
$template->addContent($mensagem);
$template->addContent($pagination->writeHtml());
$template->addContent($modal);
$template->writeHtml();
