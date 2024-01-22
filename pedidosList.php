<?php
require_once("./inc/common.php");
checkAccess("pedidosList");

$pagination = new Pagination();

$table = new Table();
$table->cardHeader(btn("Novo", "pedidosCad.php"));
$table->addHeader("Nome");
$table->addHeader("Valor",             "text-center", "col-2", false);
$table->addHeader("Valor Final",             "text-center", "col-2", false);
$table->addHeader("Quantidade",             "text-center", "col-2", false);
$table->addHeader("Categoria",             "text-center", "col-2", false);
$table->addHeader("Status",     "text-center", "col-1", false);
$table->addHeader("Ação",       "text-center", "col-1", false);

$query = new sqlQuery();
$query->addTable("cad_pedidos");
$query->addcolumn("nome");
$query->addcolumn("valor");
$query->addcolumn("quantidade");
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

$query->addOrder("id", "DESC");

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

        $valorFinal = $row["valor"] + $row["valor"] * 0.20;


        $table->addCol(btn($row['nome'], ["pedidosCad.php", ["cad_pedidos_id" => $row["id"]]], "btn-link ps-0 fw-normal edit"));
        $table->addCol("R$ " . number_format($row["valor"], 2, ",", "."), "text-end");
        $table->addCol("R$ " . number_format($valorFinal, 2, ",", "."), "text-end");
        $table->addCol($row["quantidade"], "text-center");
        $table->addCol(badge($row['cad_categoria_id'], "primary"), "text-center");
        $table->addCol($status, "text-center");
        if ($row["status"] != 1) {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["pedidosCad.php", ["cad_pedidos_id" => $row["id"]]], "transparent", "btn-sm btn-outline-danger mx-1 edit") . btn("<i class='fa-solid fa-trash'></i>", ["pedidosCadSave.php", ["cad_pedidos_id_delete" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        } else {
            $table->addCol(btn("<i class='fa-regular fa-pen-to-square'></i>", ["pedidosCad.php", ["cad_pedidos_id" => $row["id"]]], NULL, "btn-sm edit"), "text-center");
        }
        $table->endRow();
    }

    $despesa = getDbValue('SELECT SUM(valor * quantidade) FROM cad_pedidos');
    $receita = getDbValue('SELECT SUM(valor * quantidade * 1.2) FROM cad_pedidos');
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
    $resultados->addTable("cad_pedidos e");
    $resultados->addJoin("cad_categorias c", "e.cad_categoria_id = c.id");
    $resultados->addColumn("c.nome AS categoria");
    $resultados->addColumn("e.nome AS produto");
    $resultados->addColumn("e.valor");
    $resultados->addColumn("e.quantidade");
    $resultados->addWhere("e.status", "=", "'1'");
    $resultados->addOrder("c.id, e.nome");

    $formatado = [];

    if ($conn->query($resultados->getSQL()) && getDbValue($resultados->getCount()) != 0) {
        foreach ($conn->query($resultados->getSQL()) as $row) {
            $categoria = $row['categoria'];
            $produtoNome = $row['produto'];
            $valorProduto = $row['valor'] * 1.2;

            // Determinar o emoji com base na categoria
            $emoji = '';
            if (stripos($categoria, 'energético') !== false) {
                $emoji = '🔋';
            } elseif (stripos($categoria, 'refrigerante') !== false) {
                $emoji = '🥤';
            } elseif (stripos($categoria, 'cerveja') !== false) {
                $emoji = '🍺';
            }

            $produto_valor = $emoji . ' ' . $produtoNome . ' - *R$ ' . number_format($valorProduto, 2, ",", ".") . '*';

            if (!isset($formatado[$categoria])) {
                $formatado[$categoria] = [];
            }

            $formatado[$categoria][] = $produto_valor;
        }
    }

    $texto = '
    *🌙 Seja bem-vindo à ML´s Conveniência de Bebidas! 🌙*

    Conheça nossas *ofertas especiais* para tornar suas noites ainda mais incríveis:
    ';

    foreach ($formatado as $categoria => $produtos) {
        $texto .= "\n \t *" . $categoria . "*\n";

        foreach ($produtos as $produto) {
            $texto .= "\n \t \t" . $produto . "\n";
        }
    }

    $texto .= '
    🧊 Todas as bebidas entregues *geladas!*

    🚛 Entregamos durante as *madrugadas de terça a domingo!*

    🏠 *Sem valor mínimo de pedido e sem taxa de entrega nos condomínios Salomoni!*

    💸 Pagamento somente em Pix ou com o valor exato do pedido.

    🚫 Não trabalhamos com troco!

    *Chave Pix:* 04695293005

    Faça seu pedido agora e aproveite a noite com as *melhores bebidas!* 🌙✨

    *Entre em contato em:*

    https://wa.me/5551994442101
    ou
    https://wa.me/5551995534873
    ';
} else {
    $table->addCol("Nenhum registro encontrado!", "text-center", count($table->getHeaders()));
    $table->endRow();
}

$template = new Template("Listagem de Itens no pedidos");
$template->addBreadcrumb("Dashboard", "index.php");
//$template->addContent($content);
$template->addContent($table->writeHtml());
$template->addContent($mensagem);
$template->addContent($pagination->writeHtml());
$template->addContent('
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
');
$template->writeHtml();
