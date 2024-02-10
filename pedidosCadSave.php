<?php

require_once("./inc/common.php");
checkAccess("pedidosList");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);

try {
    $cad_pedido_id = getParam("cad_pedido_id");

    if (!empty($cad_pedido_id)) {
        $f_ativo = getParam("f_ativo") == "on" ? "1" : "0";
        $dados = array("status" => $f_ativo, "id" => $cad_pedido_id);

        $sql_update = "UPDATE cad_pedidos SET status = :status, dt_update = NOW() WHERE id = :id";
        $stmt = $conn->prepare($sql_update);
        $stmt->execute($dados);
        $lastInsertId = $cad_pedido_id;
        $tipo = 'success';
        $actionText = "Alteração efetuada com sucesso";
    } else {
        $porcentagem = PORCENTAGEM;
        $cad_pedido_id = getParam("cad_pedido_id");
        $cad_cliente_id = explode(' / ', getParam('cad_cliente_id'));
        $cad_cliente_id = getDBvalue("SELECT id FROM cad_clientes WHERE nome LIKE '%" . $cad_cliente_id[0] . "%'");
        $f_produtos = getParam("cad_estoque_id");
        $f_quantidade = getParam("f_quantidade");
        $f_ativo = getParam("f_ativo") == "on" ? "1" : "0";

        // Verificar se a quantidade solicitada está disponível no estoque
        $quantidade_disponivel = 1;

        if (1 <= $quantidade_disponivel) {

            $dados = array(
                "cad_cliente_id" => $cad_cliente_id,
                "status" => $f_ativo
            );

            $sql_insert = "
                    INSERT INTO cad_pedidos (
                        cad_cliente_id,
                        status
                    ) VALUES (
                        :cad_cliente_id, 
                        :status
                )";

            $stmt = $conn->prepare($sql_insert);
            $stmt->execute($dados);
            $lastInsertId = $conn->lastInsertId();

            if (!empty($lastInsertId)) {

                $sql_delete_produtos = "DELETE FROM pedidos_has_produtos WHERE cad_pedido_id = :cad_pedido_id";
                $stmt = $conn->prepare($sql_delete_produtos);
                $stmt->execute(['cad_pedido_id' => $lastInsertId]);

                $valor_total_pedido = 0;

                foreach ($f_produtos as $index => $cad_estoque_id) {

                    if (isset($f_quantidade[$index])) {

                        $quantidade = $f_quantidade[$index];

                        $dados_produtos = array(
                            "cad_pedido_id" => $lastInsertId,
                            "cad_estoque_id" => $cad_estoque_id,
                            "quantidade" => $quantidade
                        );

                        $sql_insert_produtos = "
                            INSERT INTO pedidos_has_produtos(
                                cad_pedido_id,
                                cad_estoque_id,
                                quantidade
                            ) VALUES (
                                :cad_pedido_id,
                                :cad_estoque_id,
                                :quantidade
                            )";

                        $stmt = $conn->prepare($sql_insert_produtos);
                        $stmt->execute($dados_produtos);

                        $sql_quantidade = "SELECT quantidade FROM cad_estoque WHERE id = :cad_estoque_id";
                        $stmt_quantidade = $conn->prepare($sql_quantidade);
                        $stmt_quantidade->execute(['cad_estoque_id' => $cad_estoque_id]);
                        $quantidade_disponivel = $stmt_quantidade->fetchColumn();

                        if ($quantidade_disponivel !== false) {
                            $nova_quantidade = $quantidade_disponivel - $quantidade;
                            $status = ($nova_quantidade == 0) ? '0' : '1';

                            $stmt_update = $conn->prepare("UPDATE cad_estoque SET quantidade = :nova_quantidade, status = :status WHERE id = :cad_estoque_id");
                            $stmt_update->execute(array("nova_quantidade" => $nova_quantidade, "status" => $status, "cad_estoque_id" => $cad_estoque_id));
                        }

                        $sql_valor_item = "SELECT valor_cobrado FROM cad_estoque WHERE id = :cad_estoque_id";
                        $stmt_valor_item = $conn->prepare($sql_valor_item);
                        $stmt_valor_item->execute(['cad_estoque_id' => $cad_estoque_id]);
                        $valor_item = $stmt_valor_item->fetchColumn();

                        $valor_total_pedido += $valor_item * $quantidade;
                    }
                }

                $sql_update_valor_pedido = "UPDATE cad_pedidos SET valor = :valor_total WHERE id = :cad_pedido_id";
                $stmt_update_valor_pedido = $conn->prepare($sql_update_valor_pedido);
                $stmt_update_valor_pedido->execute(['valor_total' => $valor_total_pedido, 'cad_pedido_id' => $lastInsertId]);

                $tipo = 'success';
                $actionText = "Cadastro efetuado com sucesso";
            } else {
                $tipo = 'error';
                $actionText = "Quantidade solicitada não disponível no estoque";
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
