<?php

require_once("./inc/common.php");
checkAccess("estoqueList");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_estoque_id_delete = $e["cad_estoque_id_delete"];

try {

	if (!empty($cad_estoque_id_delete)) {
		$sql_delete = "DELETE FROM cad_estoque WHERE id = :id";
		$stmt = $conn->prepare($sql_delete);
		$stmt->execute(['id' => $cad_estoque_id_delete]);
		$actionText = "Exclusão efetuada com sucesso";
		$tipo = 'success';
	} else {
		$cad_estoque_id 		= getParam("cad_estoque_id");
		$f_nome                 = getParam("f_nome");
		$f_valor 			    = floatval(str_replace(array('.', ',', 'R$'), array('', '.', ''), getParam("f_valor")));
		$f_valor_cobrado		= arredondaValor($f_valor);
		$f_quantidade       	= getParam("f_quantidade");
		$cad_categoria_id       = getParam("f_categoria");
		$f_ativo 				= getParam("f_ativo") == "on" ? "1" : "0";

		$dados = array(
			"nome"          				=> $f_nome,
			"valor"    						=> $f_valor,
			"valor_cobrado"    				=> $f_valor_cobrado,
			"quantidade"                	=> $f_quantidade,
			"cad_categoria_id"              => $cad_categoria_id,
			"status"         				=> $f_ativo,
		);

		if (!empty($cad_estoque_id)) {
			$dados["id"] = $cad_estoque_id;

			$sql_update = "
				UPDATE cad_estoque SET
					nome = :nome,
					valor = :valor,
					valor_cobrado = :valor_cobrado,
					quantidade = :quantidade,
					cad_categoria_id = :cad_categoria_id,
					status = :status
				WHERE
					id = :id
			";

			$stmt = $conn->prepare($sql_update);
			$stmt->execute($dados);
			$lastInsertId = $cad_estoque_id;
			$actionText = "Alteração efetuada com sucesso";
		} else {

			$sql_insert = "
				INSERT INTO cad_estoque (
					nome,
					valor,
					valor_cobrado,
					quantidade,
					cad_categoria_id,
					status
				) VALUES (
					:nome, 
					:valor,
					:valor_cobrado,
					:quantidade,
					:cad_categoria_id,
					:status
			)";

			$stmt = $conn->prepare($sql_insert);
			$stmt->execute($dados);
			$lastInsertId = $conn->lastInsertId();
			$actionText = "Cadastro efetuado com sucesso";
		}

		if (!empty($lastInsertId)) {
			$dados = array(
				"produto"           => getDbValue("SELECT nome FROM cad_estoque WHERE id =" . $lastInsertId),
				"valor"          => getDbValue("SELECT valor FROM cad_estoque WHERE id =" . $lastInsertId)
			);

			$sql_insert = "
					INSERT INTO historico_estoque (
						produto,
						valor,
						dt_create
					) VALUES (
						:produto,
						:valor,
						NOW()
					)";

			$stmt = $conn->prepare($sql_insert);
			$stmt->execute($dados);
		}

		$tipo = 'success';
	}
} catch (PDOException $e) {
	if (!empty($cad_estoque_id)) {
		$actionText = "Erro ao alterar";
	} else {
		$actionText = "Erro ao cadastrar";
	}

	$extend = "text: 'Desculpe, ocorreu um erro";
	if (!empty($cad_estoque_id) || !empty($cad_estoque_id_delete)) {
		$extend .= " na ";
		if (!empty($cad_estoque_id)) {
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
	if (!empty($cad_estoque_id)) {
		writeLogs("Action: UPDATE SQL", "error");
		writeLogs(printSQL($sql_update, $dados, true), "error");
	} else if (!empty($cad_estoque_id_delete)) {
		writeLogs("Action: DELETE SQL", "error");
		writeLogs(printSQL($sql_delete, ['id' => $cad_estoque_id_delete], true), "error");
	} else {
		writeLogs("Action: INSERT SQL", "error");
		writeLogs(printSQL($sql_insert, $dados, true), "error");
	}
	writeLogs(print_r($e, true), "error");
}



setAlert($actionText, $tipo, $extend);
redirect("estoqueList.php");
