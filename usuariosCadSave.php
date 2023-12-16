<?php

require_once("./inc/common.php");
checkAccess("usuariosList");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_usuario_id_delete = $e["cad_usuario_id_delete"];
$cad_usuario_id_status = $e["cad_usuario_id_status"];

try {
	if (!empty($cad_usuario_id_delete)) {
		$dados = array(
			"nome"           => getDbValue("SELECT nome FROM cad_usuarios WHERE id =" . $cad_usuario_id_delete),
			"email"          => getDbValue("SELECT email FROM cad_usuarios WHERE id =" . $cad_usuario_id_delete),
			"responsible" 	 => getUserInfo("id")
		);

		$sql_insert = "
                INSERT INTO historico_usuarios (
					nome,
					email,
					dt_delete,
					responsible
                ) VALUES (
					:nome,
					:email,
					NOW(),
					:responsible
                )";

		$stmt = $conn->prepare($sql_insert);
		$stmt->execute($dados);

		$sql_delete = "DELETE FROM cad_usuarios WHERE id = :id";
		$stmt = $conn->prepare($sql_delete);
		$stmt->execute(['id' => $cad_usuario_id_delete]);
		$actionText = "Exclusão efetuada com sucesso";
		$tipo = 'success';
	} else if (!empty($cad_usuario_id_status)) {
		$dados = array(
			"id" 	 => getUserInfo("id"),
			"status" => 0
		);

		$sql_update = "UPDATE cad_usuarios SET status = :status, dt_trash = NOW() WHERE id = :id";
		$stmt = $conn->prepare($sql_update);
		$stmt->execute($dados);
		$actionText = "Conta desativada com sucesso";
		redirect("loginSair.php");
	} else {
		$cad_usuario_id 		= getParam("cad_usuario_id");
		$f_imagem               = json_decode(getParam("f_imagem"), true);
		$f_nome 				= getParam("f_nome");
		$f_email                = strtolower(getParam("f_email"));
		$cad_cargo_id           = getParam("f_cargo");
		$f_senha                = getParam("f_senha");
		$f_confirmar_senha      = getParam("f_confirmar_senha");
		$f_ativo 				= getParam("f_ativo") == "on" ? "1" : "0";
		$f_tipo 				= 0;

		$dados = array(
			"nome"           => $f_nome,
			"email"          => $f_email,
			"cad_cargo_id"   => $cad_cargo_id,
			"status"         => $f_ativo,
		);


		validar_email($f_email);

		if (!empty($f_senha) && !empty($f_confirmar_senha) && !empty($cad_usuario_id)) {

			$dados_pass = array(
				"id"             => $cad_usuario_id,
				"senha"      	 => validar_senha($f_senha, $f_confirmar_senha),
			);

			$sql_update_pass = "UPDATE cad_usuarios SET senha = :senha WHERE id = :id";

			$stmt = $conn->prepare($sql_update_pass);
			$stmt->execute($dados_pass);
			$actionText = "Alteração efetuada com sucesso";
		} else {
			if (!empty($cad_usuario_id)) {
				$dados["id"] = $cad_usuario_id;

				$sql_update = "
				UPDATE cad_usuarios SET
					nome = :nome,
					email = :email,
					cad_cargo_id = :cad_cargo_id,
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
				$lastInsertId = $cad_usuario_id;
				$actionText = "Alteração efetuada com sucesso";
			} else {
				$dados["uniqid"] = uniqIdNew();

				if (!empty($f_senha) && !empty($f_confirmar_senha)) {
					$dados["senha"] = validar_senha($f_senha, $f_confirmar_senha);
				}

				$sql_insert = "
				INSERT INTO cad_usuarios (
					uniqid, 
					nome,
					email,
					cad_cargo_id,
					senha,
					dt_create,
					status
				";

				$sql_insert .= "
				) VALUES (
				:uniqid,
				:nome,
				:email,				
				:cad_cargo_id,
				:senha,
				NOW(),
				:status
				";

				$sql_insert .= ")";

				$stmt = $conn->prepare($sql_insert);
				$stmt->execute($dados);
				$lastInsertId = $conn->lastInsertId();
				$actionText = "Cadastro efetuado com sucesso";
			}
		}


		if (isset($_FILES['imagem'])) {
			$file = $_FILES['imagem'];
			$uploadDir = 'uploads/'; // Diretório onde as imagens serão armazenadas
			$uploadPath = $uploadDir . basename($file['name']);

			// Mover o arquivo para o diretório de upload
			if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
				echo 'Upload bem-sucedido!';
			} else {
				echo 'Erro ao fazer o upload do arquivo.';
			}
		}


		if (!empty($f_imagem['data'])) {
			$imagemBase64 = $f_imagem['data'];

			$dados = array(
				"id" => $lastInsertId,
				"imagem" => $imagemBase64,
			);

			$sql_update = "
				UPDATE cad_usuarios SET
				imagem = :imagem
				WHERE
				id = :id
			";

			$conn->prepare($sql_update)->execute($dados);
		}

		$tipo = 'success';
	}
} catch (PDOException $e) {
	if (!empty($cad_usuario_id_delete)) {
		$actionText = "Erro ao excluir";
	} else if (!empty($cad_usuario_id)) {
		$actionText = "Erro ao alterar";
	} else {
		$actionText = "Erro ao cadastrar";
	}

	$extend = "text: 'Desculpe, ocorreu um erro";
	if (!empty($cad_usuario_id) || !empty($cad_usuario_id_delete)) {
		$extend .= " na ";
		if (!empty($cad_usuario_id)) {
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
	if (!empty($cad_usuario_id)) {
		writeLogs("Action: UPDATE SQL", "error");
		writeLogs(printSQL($sql_update, $dados, true), "error");
	} else if (!empty($cad_usuario_id_delete)) {
		writeLogs("Action: DELETE SQL", "error");
		writeLogs(printSQL($sql_delete, ['id' => $cad_usuario_id_delete], true), "error");
	} else {
		writeLogs("Action: INSERT SQL", "error");
		writeLogs(printSQL($sql_insert, $dados, true), "error");
	}
	writeLogs(print_r($e, true), "error");
}

setAlert($actionText, $tipo, $extend);
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'perfil.php') !== false) {
	redirect(btnLink(["perfil.php", ["cad_usuario_id" => $cad_usuario_id]]));
} else {
	redirect("usuariosList.php");
}
