<?php

require_once("./inc/common.php");
checkAccess();

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_cliente_id_delete = $e["cad_cliente_id_delete"];
$cad_cliente_id_status = $e["cad_cliente_id_status"];

try {
	if (!empty($cad_cliente_id_delete)) {
		$dados = array(
			"nome"           => getDbValue("SELECT nome FROM cad_clientes WHERE id =" . $cad_cliente_id_delete),
			"email"          => getDbValue("SELECT email FROM cad_clientes WHERE id =" . $cad_cliente_id_delete),
			"celular"        => getDbValue("SELECT celular FROM cad_clientes WHERE id =" . $cad_cliente_id_delete),
			"responsible" 	 => getUserInfo("id")
		);

		$sql_insert = "
                INSERT INTO historico_usuarios (
					nome,
					email,
					celular,
					dt_delete,
					responsible
                ) VALUES (
					:nome,
					:email,
					:celular,
					NOW(),
					:responsible
                )";

		$stmt = $conn->prepare($sql_insert);
		$stmt->execute($dados);

		$sql_delete = "DELETE FROM cad_clientes WHERE id = :id";
		$stmt = $conn->prepare($sql_delete);
		$stmt->execute(['id' => $cad_cliente_id_delete]);
		$actionText = "Exclusão efetuada com sucesso";
		$tipo = 'success';
	} else if (!empty($cad_cliente_id_status)) {
		$dados = array(
			"id" 	 => getUserInfo("id"),
			"status" => 0
		);

		$sql_update = "UPDATE cad_clientes SET status = :status, dt_trash = NOW() WHERE id = :id";
		$stmt = $conn->prepare($sql_update);
		$stmt->execute($dados);
		$actionText = "Conta desativada com sucesso";
		redirect("loginSair.php");
	} else {
		$cad_cliente_id 		= getParam("cad_cliente_id");
		$f_imagem               = getFileParam("f_imagem");
		$f_nome 				= getParam("f_nome");
		$f_email                = strtolower(getParam("f_email"));
		$f_celular             	= getParam("f_celular");
		$f_cep                  = str_replace('-', '', getParam("f_cep"));
		$f_estado               = getParam("f_uf");
		$f_cidade               = getParam("f_localidade");
		$f_bairro               = getParam("f_bairro");
		$f_logradouro           = getParam("f_logradouro");
		$f_numero               = getParam("f_numero");
		$f_complemento          = getParam("f_complemento");
		$f_senha                = getParam("f_senha");
		$f_confirmar_senha      = getParam("f_confirmar_senha");
		$f_ativo 				= getParam("f_ativo") == "on" ? "1" : "0";

		$dados = array(
			"nome"           		=> $f_nome,
			"email"          		=> $f_email,
			"celular"        		=> $f_celular,
			"cep"            		=> $f_cep,
			"estado"         		=> $f_estado,
			"cidade"         		=> $f_cidade,
			"bairro"         		=> $f_bairro,
			"logradouro"     		=> $f_logradouro,
			"numero"         		=> $f_numero,
			"complemento"    		=> $f_complemento,
			"status"         		=> $f_ativo
		);

		validar_email($f_email);

		if (!empty($f_senha) && !empty($f_confirmar_senha) && !empty($cad_cliente_id)) {

			$dados_pass = array(
				"id"             => $cad_cliente_id,
				"senha"      	 => validar_senha($f_senha, $f_confirmar_senha),
			);

			$sql_update_pass = "UPDATE cad_clientes SET senha = :senha WHERE id = :id";

			$stmt = $conn->prepare($sql_update_pass);
			$stmt->execute($dados_pass);
			$actionText = "Alteração efetuada com sucesso";
		} else {
			if (!empty($cad_cliente_id)) {
				$dados["id"] = $cad_cliente_id;

				$sql_update = "
				UPDATE cad_clientes SET
					nome = :nome,
					email = :email,
					celular = :celular,
					cep = :cep,
					estado = :estado,
					cidade = :cidade,
					logradouro = :logradouro,
					bairro = :bairro,
					numero = :numero,
					complemento = :complemento,
					dt_update = NOW()
				";

				if ($f_ativo == "0") {
					$sql_update .= ", dt_trash = NOW()";
				} else {
					$sql_update .= ", dt_trash = NULL";
				}

				$sql_update .= ", status = :status WHERE id = :id";

				$stmt = $conn->prepare($sql_update);
				$stmt->execute($dados);
				$lastInsertId = $cad_cliente_id;
				$actionText = "Alteração efetuada com sucesso";
			} else {
				$dados["uid"] = uniqIdNew();

				if (!empty($f_senha) && !empty($f_confirmar_senha)) {
					$dados["senha"] = validar_senha($f_senha, $f_confirmar_senha);
				}

				$sql_insert = "
                INSERT INTO cad_clientes (
                    uid, 
					nome,
					email,
					celular,
					cep,
					estado,
					cidade,
					bairro,
					logradouro,
					numero,
					complemento,
					senha,
					dt_create,
                    status
                ) VALUES (
                    :uid,
					:nome,
					:email,
					:celular,
					:cep,
					:estado,
					:cidade,
					:bairro,
					:logradouro,
					:numero,
					:complemento,
					:senha,
					NOW(),
                    :status
                )";

				$stmt = $conn->prepare($sql_insert);
				$stmt->execute($dados);
				$lastInsertId = $conn->lastInsertId();
				$actionText = "Cadastro efetuado com sucesso";
			}
		}

		if (!empty($f_imagem["tmp_name"])) {
			$imagemPath = $f_imagem["tmp_name"];
			$imagemBinaria = file_get_contents($imagemPath);
			$imagemBase64 = base64_encode($imagemBinaria);

			$dados = array(
				"id" => $lastInsertId,
				"imagem" => $imagemBase64,
			);

			$sql_update = "
				UPDATE cad_clientes SET
				imagem = :imagem
				WHERE
				id = :id
			";

			$conn->prepare($sql_update)->execute($dados);
		}

		$tipo = 'success';
	}
} catch (PDOException $e) {
	if (!empty($cad_cliente_id_delete)) {
		$actionText = "Erro ao excluir";
	} else if (!empty($cad_cliente_id)) {
		$actionText = "Erro ao alterar";
	} else {
		$actionText = "Erro ao cadastrar";
	}

	$extend = "text: 'Desculpe, ocorreu um erro";
	if (!empty($cad_cliente_id) || !empty($cad_cliente_id_delete)) {
		$extend .= " na ";
		if (!empty($cad_cliente_id)) {
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
	if (!empty($cad_cliente_id)) {
		writeLogs("Action: UPDATE SQL", "error");
		writeLogs(printSQL($sql_update, $dados, true), "error");
	} else if (!empty($cad_cliente_id_delete)) {
		writeLogs("Action: DELETE SQL", "error");
		writeLogs(printSQL($sql_delete, ['id' => $cad_cliente_id_delete], true), "error");
	} else {
		writeLogs("Action: INSERT SQL", "error");
		writeLogs(printSQL($sql_insert, $dados, true), "error");
	}
	writeLogs(print_r($e, true), "error");
}

setAlert($actionText, $tipo, $extend);
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'perfil.php') !== false) {
	redirect(btnLink(["perfil.php", ["cad_usuario_id" => $cad_cliente_id]]));
} else {
	redirect("clientesList.php");
}
