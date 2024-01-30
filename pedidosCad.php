<?php
require_once("./inc/common.php");
checkAccess("pedidosList");

$e = getParam("e", true);
$cad_pedido_id = $e["cad_pedido_id"];

$f_status = "checked";

$modal = '';

if (!empty($cad_pedido_id)) {

    $query = new sqlQuery();
    $query->addTable("cad_pedidos");
    $query->addcolumn("id");
    $query->addcolumn("cad_cliente_id");
    $query->addcolumn("(SELECT nome FROM cad_clientes WHERE id = cad_pedidos.cad_cliente_id) AS cad_cliente_id");
    $query->addcolumn("quantidade");
    $query->addcolumn("cad_estoque_id");
    $query->addcolumn("status");
    $query->addWhere("id", "=", $cad_pedido_id);

    foreach ($conn->query($query->getSQL()) as $row) {
        $cad_cliente_id = $row["cad_cliente_id"];
        $f_valor = number_format($row["valor"], 2, ",", ".");
        $f_quantidade  = $row["quantidade"];
        $cad_estoque_id = $row["cad_estoque_id"];
        $f_status = "";

        if ($row["status"] == 1) {
            $f_status = "checked";
        }
    }

    $form = new Form("pedidosCadSave.php");
    $form->setUpload(true);
    $form->addField('<h1>Pedido:  #' . $cad_pedido_id . '</h1><hr>');
    $form->addField(hiddenField($cad_pedido_id, "cad_pedido_id"));
    $form->addField(readField("Cliente:", $cad_cliente_id));
    $form->addField(readField("Produto:", getDbValue("SELECT nome FROM cad_estoque WHERE id =" . $cad_estoque_id)));
    $form->addField(readField("Quantidade:", $f_quantidade));
    $form->addField(checkboxField("Pago | Pendente", $f_status, "f_ativo"));
    $form->addField(submitBtn("Salvar"));
} else {

    $clientes = new sqlQuery();
    $clientes->addTable("cad_clientes");
    $clientes->addcolumn("id");
    $clientes->addcolumn("nome");
    $clientes->addcolumn("celular");
    $clientes->addWhere("status", "=", "1");
    $clientes->addOrder("nome", "ASC");

    if ($conn->query($clientes->getSQL()) && getDbValue($clientes->getCount()) != 0) {
        foreach ($conn->query($clientes->getSQL()) as $row) {
            $options_f_clientes[] = array("id" => $row["id"], "name" => $row["nome"] . ' / ' . $row["celular"]);
        }
    } else {
        $options_f_clientes[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $produtos = new sqlQuery();
    $produtos->addTable("cad_estoque");
    $produtos->addcolumn("id");
    $produtos->addcolumn("nome");
    $produtos->addWhere("status", "=", "1");
    $produtos->addOrder("valor", "ASC");

    if ($conn->query($produtos->getSQL()) && getDbValue($produtos->getCount()) != 0) {
        foreach ($conn->query($produtos->getSQL()) as $row) {
            $options_f_produtos[] = array("id" => $row["id"], "name" => $row["nome"]);
        }
    } else {
        $options_f_produtos[] = array("id" => NULL, "name" => "Nenhum registro encontrado!");
    }

    $botaoNovoCliente = array();

    $novoCliente = new Form("pedidosCadSave.php");
    $novoCliente->addField(textField("Nome", $f_nome, NUll, true));
    $novoCliente->addField(telField("Celular", $f_celular, NUll, false, "\(\d{2}\)\s?\d?(\d{4,5})-?\d{4}"));
    $novoCliente->addField(textField("CEP", $f_cep, NULL, true, "\d{5}-\d{3}"));
    $novoCliente->addField(textField("Estado", $f_estado, "f_uf", true, "[A-Z]{2}"));
    $novoCliente->addField(textField("Cidade", $f_cidade, "f_localidade", true));
    $novoCliente->addField(textField("Bairro", $f_bairro, NULL, true));
    $novoCliente->addField(textField("Logradouro", $f_logradouro, NULL, true));
    $novoCliente->addField(textField("Número", $f_numero, NULL, true));
    $novoCliente->addField(textField("Complemento", $f_complemento));
    $novoCliente->addField(submitBtn("Salvar"));
    $novoCliente->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));

    $botaoNovoCliente[] = '
        <div class="col-lg-2">
            <div class="d-grid mb-1">
                <label for="id_cliente_novo" class="form-label invisible">Novo Cliente</label>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoCliente">
                    <i data-feather="plus"></i> Novo Cliente
                </button>
            </div>
        </div>
    ';

    $modal = '
        <div class="modal fade" id="novoCliente" tabindex="-1" aria-labelledby="novoClienteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="novoClienteLabel">Adicionar novo cliente</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ' . $novoCliente->writeHtml() . '
                    </div>
                </div>
            </div>
        </div>
    ';

    $clienteField = '
        <div class="row align-items-center">
            <div class="col">
                ' . listDataList("Cliente", $options_f_clientes, $cad_cliente_id, "cad_cliente_id", true) . '
            </div>
        ' . implode("", $botaoNovoCliente) . '
        </div>
    ';

    $form = new Form("pedidosCadSave.php");
    $form->setUpload(true);
    $form->addField(hiddenField($cad_pedido_id, "cad_pedido_id"));
    $form->addField($clienteField);
    $form->addField(listField("Produto", $options_f_produtos, $cad_estoque_id, "cad_estoque_id", true));
    $form->addField(numberField("Quantidade", $f_quantidade, NULL, true, 1));
    $form->addField(checkboxField("Pago | Pendente", $f_status, "f_ativo"));
    $form->addField(submitBtn("Salvar"));
    $form->addField(submitBtn('Limpar', "ms-1 btn-danger", NULL, true));
}

$template = new Template("Cadastro de Pedidos");
$template->addBreadcrumb("Dashboard", "index.php");
$template->addBreadcrumb("Listagem de Itens no Pedidos", "pedidosList.php");
$template->addContent($form->writeHtml(), true);
$template->addContent($modal);
$template->writeHtml();
