<?php

require_once("./inc/common.php");
checkAccess("pedidosList");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_pedido_id_delete = $e["cad_pedido_id_delete"];

try {
	if (!empty($cad_pedido_id_delete)) {
		$sql_delete = "DELETE FROM cad_pedidos WHERE id = :id";
		$stmt = $conn->prepare($sql_delete);
		$stmt->execute(['id' => $cad_pedido_id_delete]);
		$actionText = "Exclusão efetuada com sucesso";
		$tipo = 'success';
	} else {
		$cad_pedido_id = getParam("cad_pedido_id");

		if (!empty($cad_pedido_id)) {

			$f_ativo = getParam("f_ativo") == "on" ? "1" : "0";

			$dados = array(
				"status" => $f_ativo
			);

			$dados["id"] = $cad_pedido_id;

			$sql_update = "
				UPDATE cad_pedidos SET
					status = :status
				WHERE
					id = :id
			";

			$stmt = $conn->prepare($sql_update);
			$stmt->execute($dados);
			$lastInsertId = $cad_pedido_id;
			$actionText = "Alteração efetuada com sucesso";
		} else {

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
			$actionText = "Cadastro efetuado com sucesso";
		}

		$tipo = 'success';
	}
} catch (PDOException $e) {
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
