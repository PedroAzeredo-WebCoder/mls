<?php

require_once("./inc/common.php");
checkAccess("categoriasList");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_categoria_id_delete = $e["cad_categoria_id_delete"];

try {
	if (!empty($cad_categoria_id_delete)) {
		$sql_delete = "DELETE FROM cad_categorias WHERE id = :id";
		$stmt = $conn->prepare($sql_delete);
		$stmt->execute(['id' => $cad_categoria_id_delete]);
		$actionText = "Exclusão efetuada com sucesso";
		$tipo = 'success';
	} else {
		$cad_categoria_id 		= getParam("cad_categoria_id");
		$f_icone                 = getParam("f_icone");
		$f_nome                 = getParam("f_nome");
		$f_ativo 				= getParam("f_ativo") == "on" ? "1" : "0";

		$dados = array(
			"icone"          				=> $f_icone,
			"nome"          				=> $f_nome,
			"status"         				=> $f_ativo,
		);

		if (!empty($cad_categoria_id)) {
			$dados["id"] = $cad_categoria_id;

			$sql_update = "
				UPDATE cad_categorias SET
					icone = :icone,
					nome = :nome,
					status = :status
				WHERE
					id = :id
			";

			$stmt = $conn->prepare($sql_update);
			$stmt->execute($dados);
			$lastInsertId = $cad_categoria_id;
			$actionText = "Alteração efetuada com sucesso";
		} else {

			$sql_insert = "
				INSERT INTO cad_categorias (
					icone,
					nome,
					status
				) VALUES (
					:icone, 
					:nome, 
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
	if (!empty($cad_categoria_id)) {
		$actionText = "Erro ao alterar";
	} else {
		$actionText = "Erro ao cadastrar";
	}

	$extend = "text: 'Desculpe, ocorreu um erro";
	if (!empty($cad_categoria_id) || !empty($cad_categoria_id_delete)) {
		$extend .= " na ";
		if (!empty($cad_categoria_id)) {
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
	if (!empty($cad_categoria_id)) {
		writeLogs("Action: UPDATE SQL", "error");
		writeLogs(printSQL($sql_update, $dados, true), "error");
	} else if (!empty($cad_categoria_id_delete)) {
		writeLogs("Action: DELETE SQL", "error");
		writeLogs(printSQL($sql_delete, ['id' => $cad_categoria_id_delete], true), "error");
	} else {
		writeLogs("Action: INSERT SQL", "error");
		writeLogs(printSQL($sql_insert, $dados, true), "error");
	}
	writeLogs(print_r($e, true), "error");
}



setAlert($actionText, $tipo, $extend);
redirect("categoriasList.php");
