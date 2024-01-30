<?php

require_once("./inc/common.php");
checkAccess("pedidosList");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_pedido_id_delete = $e["cad_pedido_id_delete"];

try {
    if (!empty($cad_pedido_id_delete)) {
        // Exclusão de pedido
        $sql_delete = "DELETE FROM cad_pedidos WHERE id = :id";
        $stmt = $conn->prepare($sql_delete);
        $stmt->execute(['id' => $cad_pedido_id_delete]);
        $tipo = 'success';
        $actionText = "Exclusão efetuada com sucesso";
    } else {
        // Atualização ou inserção de pedido
        $cad_pedido_id = getParam("cad_pedido_id");

        if (!empty($cad_pedido_id)) {
            // Atualização de status do pedido
            $f_ativo = getParam("f_ativo") == "on" ? "1" : "0";
            $dados = array("status" => $f_ativo, "id" => $cad_pedido_id);

            $sql_update = "UPDATE cad_pedidos SET status = :status WHERE id = :id";
            $stmt = $conn->prepare($sql_update);
            $stmt->execute($dados);
            $lastInsertId = $cad_pedido_id;
			$tipo = 'success';
            $actionText = "Alteração efetuada com sucesso";
        } else {
            // Inserção de novo pedido
            $porcentagem = PORCENTAGEM;
            $cad_pedido_id = getParam("cad_pedido_id");
            $cad_cliente_id = explode(' / ', getParam('cad_cliente_id'));
            $cad_cliente_id = getDBvalue("SELECT id FROM cad_clientes WHERE nome LIKE '%" . $cad_cliente_id[0] . "%'");
            $cad_estoque_id = getParam("cad_estoque_id");
            $f_quantidade = getParam("f_quantidade");
            $valor = getDbValue("SELECT valor FROM cad_estoque WHERE id = " . $cad_estoque_id);
            $f_valor = floatval(($valor + ($valor * $porcentagem)) * $f_quantidade);
            $f_ativo = getParam("f_ativo") == "on" ? "1" : "0";

            // Verificar se a quantidade solicitada está disponível no estoque
            $quantidade_disponivel = getDbValue("SELECT quantidade FROM cad_estoque WHERE id = " . $cad_estoque_id);

            if ($f_quantidade <= $quantidade_disponivel) {
                // Quantidade disponível, proceda com a inserção do pedido
                $dados = array(
                    "cad_cliente_id" => $cad_cliente_id,
                    "cad_estoque_id" => $cad_estoque_id,
                    "quantidade" => $f_quantidade,
                    "valor" => number_format($f_valor, 2),
                    "status" => $f_ativo
                );

                $sql_insert = "
                    INSERT INTO cad_pedidos (
                        cad_cliente_id,
                        cad_estoque_id,
                        quantidade,
                        valor,
                        status
                    ) VALUES (
                        :cad_cliente_id, 
                        :cad_estoque_id,
                        :quantidade,
                        :valor,
                        :status
                )";

                $stmt = $conn->prepare($sql_insert);
                $stmt->execute($dados);
                $lastInsertId = $conn->lastInsertId();
				$tipo = 'success';
                $actionText = "Cadastro efetuado com sucesso";

                // Atualizar quantidade no estoque
                $nova_quantidade = $quantidade_disponivel - $f_quantidade;
                $stmt = $conn->prepare("UPDATE cad_estoque SET quantidade = :nova_quantidade, status = :status WHERE id = :cad_estoque_id");
                $stmt->execute(array("nova_quantidade" => $nova_quantidade, "status" => ($nova_quantidade == 0) ? '0' : '1', "cad_estoque_id" => $cad_estoque_id));
            } else {
                // Quantidade indisponível no estoque
                $tipo = 'error';
                $actionText = "Quantidade solicitada não disponível no estoque";
                // Adicione aqui a lógica adicional conforme necessário
            }
        }
    }
} catch (PDOException $e) {
    // Tratamento de erros
    if (!empty($cad_pedido_id)) {
        $actionText = "Erro ao alterar";
    } else {
        $actionText = "Erro ao cadastrar";
    }

    $extend = "text: 'Desculpe, ocorreu um erro";

    if (!empty($cad_pedido_id) || !empty($cad_pedido_id_delete)) {
        $extend .= " na ";
        if (!empty($cad_pedido_id)) {
            $extend .= "alteração";
        } else {
            $extend .= "exclusão";
        }
        $extend .= ".";
    } else {
        $extend .= " no cadastro.";
    }

    $extend .= " Por favor, verifique os campos obrigatórios e/ou os dados inseridos. É possível que alguns dados já tenham sido utilizados.'";
    $tipo = 'error';

    writeLogs("==== " . __FILE__ . " ====", "error");

    if (!empty($cad_pedido_id)) {
        writeLogs("Action: UPDATE SQL", "error");
        writeLogs(printSQL($sql_update, $dados, true), "error");
    } else if (!empty($cad_pedido_id_delete)) {
        writeLogs("Action: DELETE SQL", "error");
        writeLogs(printSQL($sql_delete, ['id' => $cad_pedido_id_delete], true), "error");
    } else {
        writeLogs("Action: INSERT SQL", "error");
        writeLogs(printSQL($sql_insert, $dados, true), "error");
    }

    writeLogs(print_r($e, true), "error");
}

setAlert($actionText, $tipo, $extend);
redirect("pedidosList.php");
