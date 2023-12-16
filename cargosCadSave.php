<?php

require_once("./inc/common.php");
checkAccess("cargosList");

$e = getParam("e", true);
$cad_cargo_id_delete = $e["cad_cargo_id_delete"];

try {
	if (!empty($cad_cargo_id_delete)) {
		$sql_delete = "DELETE FROM cad_cargos WHERE id = :id";
		$stmt = $conn->prepare($sql_delete);
		$stmt->execute(['id' => $cad_cargo_id_delete]);
		$actionText = "Exclusão efetuada com sucesso";
		$tipo = 'success';
	} else {
		$cad_cargo_id = getParam("cad_cargo_id");
		$f_nome = getParam("f_nome");
		$f_permissoes = getParam("f_permissoes");
		$f_ativo = getParam("f_ativo") == "on" ? "1" : "0";

		$dados = array(
			"nome" => $f_nome,
			"status" => $f_ativo
		);

		if (!empty($cad_cargo_id)) {
			$dados["id"] = $cad_cargo_id;

			$sql_update = "
                UPDATE cad_cargos SET
                    nome = :nome,
                    status = :status
                WHERE
                    id = :id
            ";

			$stmt = $conn->prepare($sql_update);
			$stmt->execute($dados);
			$actionText = "Alteração efetuada com sucesso";
		} else {
			$sql_insert = "
                INSERT INTO cad_cargos (
                    nome,
                    status
                ) VALUES (
                    :nome,
                    :status
                )";

			$stmt = $conn->prepare($sql_insert);
			$stmt->execute($dados);
			$cad_cargo_id = $conn->lastInsertId();
			$actionText = "Cadastro efetuado com sucesso";
		}

		if (!empty($f_permissoes)) {
			$sql_delete_permissoes = "DELETE FROM cargos_has_permissoes WHERE cad_cargo_id = :cad_cargo_id";
			$stmt = $conn->prepare($sql_delete_permissoes);
			$stmt->execute(['cad_cargo_id' => $cad_cargo_id]);

			foreach ($f_permissoes as $permissao) {
				$dados_permissoes = array(
					"cad_cargo_id" => $cad_cargo_id,
					"adm_menu_id" => $permissao,
				);
				$sql_insert_permissoes = "
                    INSERT INTO cargos_has_permissoes (
                        cad_cargo_id,
                        adm_menu_id
                    ) VALUES (
                        :cad_cargo_id,
                        :adm_menu_id
                    )";
				$stmt = $conn->prepare($sql_insert_permissoes);
				$stmt->execute($dados_permissoes);
			}
		}

		$tipo = 'success';
	}
} catch (PDOException $e) {
	if (!empty($cad_cargo_id_delete)) {
		$actionText = "Erro ao excluir";
	} else if (!empty($cad_cargo_id)) {
		$actionText = "Erro ao alterar";
	} else {
		$actionText = "Erro ao cadastrar";
	}

	$extend = "text: 'Desculpe, ocorreu um erro";
	if (!empty($cad_cargo_id) || !empty($cad_cargo_id_delete)) {
		$extend .= " na ";
		if (!empty($cad_cargo_id)) {
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
	if (!empty($cad_cargo_id)) {
		writeLogs("Action: UPDATE SQL", "error");
		writeLogs(printSQL($sql_update, $dados, true), "error");
	} else if (!empty($cad_cargo_id_delete)) {
		writeLogs("Action: DELETE SQL", "error");
		writeLogs(printSQL($sql_delete, ['id' => $cad_cargo_id_delete], true), "error");
	} else {
		writeLogs("Action: INSERT SQL", "error");
		writeLogs(printSQL($sql_insert, $dados, true), "error");
	}
	writeLogs(print_r($e, true), "error");
}



setAlert($actionText, $tipo, $extend);
redirect("cargosList.php");
