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
			"nome"           => getDbValue("SELECT nome FROM cad_usuarios WHERE id =" . $cad_cliente_id_delete),
			"documento"      => getDbValue("SELECT documento FROM cad_usuarios WHERE id =" . $cad_cliente_id_delete),
			"email"          => getDbValue("SELECT email FROM cad_usuarios WHERE id =" . $cad_cliente_id_delete),
			"celular"        => getDbValue("SELECT celular FROM cad_usuarios WHERE id =" . $cad_cliente_id_delete),
			"responsible" 	 => getUserInfo("id")
		);

		$sql_insert = "
                INSERT INTO historico_usuarios (
					nome,
					documento,
					email,
					celular,
					dt_delete,
					responsible
                ) VALUES (
					:nome,
					:documento,
					:email,
					:celular,
					NOW(),
					:responsible
                )";

		$stmt = $conn->prepare($sql_insert);
		$stmt->execute($dados);

		$sql_delete = "DELETE FROM cad_usuarios WHERE id = :id";
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
		$f_documento            = str_replace(array('.', '-', '/'), array('', '', ''), getParam("f_documento"));
		$f_email                = strtolower(getParam("f_email"));
		$f_celular             	= getParam("f_celular");
		$cad_cargo_id           = getDbValue("SELECT id FROM cad_cargos WHERE nome LIKE '%Clientes%'");
		$f_dt_nascimento        = getParam("f_dt_nascimento");
		$f_cep                  = str_replace('-', '', getParam("f_cep"));
		$f_estado               = getParam("f_uf");
		$f_cidade               = getParam("f_localidade");
		$f_bairro               = getParam("f_bairro");
		$f_logradouro           = getParam("f_logradouro");
		$f_numero               = getParam("f_numero");
		$f_complemento          = getParam("f_complemento");
		$f_parente_nome         = getParam("f_parente_nome");
		$f_parente_parentesco   = getParam("f_parente_parentesco");
		$f_parente_celular      = getParam("f_parente_celular");
		$cad_origem_id      	= getParam("cad_origem_id");
		$f_objetivos 			= getParam("f_objetivos");
		$f_informacoes_saude    = getParam("f_informacoes_saude");
		$f_senha                = getParam("f_senha");
		$f_confirmar_senha      = getParam("f_confirmar_senha");
		$f_ativo 				= getParam("f_ativo") == "on" ? "1" : "0";
		$f_tipo 				= 1;

		$dados = array(
			"nome"           		=> $f_nome,
			"documento"      		=> $f_documento,
			"email"          		=> $f_email,
			"celular"        		=> $f_celular,
			"cad_cargo_id"   		=> $cad_cargo_id,
			"dt_nascimento"  		=> $f_dt_nascimento,
			"cep"            		=> $f_cep,
			"estado"         		=> $f_estado,
			"cidade"         		=> $f_cidade,
			"bairro"         		=> $f_bairro,
			"logradouro"     		=> $f_logradouro,
			"numero"         		=> $f_numero,
			"complemento"    		=> $f_complemento,
			"parente_nome"   		=> $f_parente_nome,
			"parente_parentesco"    => $f_parente_parentesco,
			"parente_celular"    	=> $f_parente_celular,
			"cad_origem_id"    		=> $cad_origem_id,
			"status"         		=> $f_ativo,
			"tipo"         	 		=> $f_tipo
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
				UPDATE cad_usuarios SET
					nome = :nome,
					documento = :documento,
					email = :email,
					celular = :celular,
					cad_cargo_id = :cad_cargo_id,
					dt_nascimento = :dt_nascimento,
					cep = :cep,
					estado = :estado,
					cidade = :cidade,
					logradouro = :logradouro,
					bairro = :bairro,
					numero = :numero,
					complemento = :complemento,
					parente_nome = :parente_nome,
					parente_parentesco = :parente_parentesco,
					parente_celular = :parente_celular,
					cad_origem_id = :cad_origem_id,
					tipo = :tipo,
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
				$dados["uniqid"] = uniqIdNew();

				if (!empty($f_senha) && !empty($f_confirmar_senha)) {
					$dados["senha"] = validar_senha($f_senha, $f_confirmar_senha);
				}

				$sql_insert = "
                INSERT INTO cad_usuarios (
                    uniqid, 
					nome,
					documento,
					email,
					celular,
					cad_cargo_id,
					dt_nascimento,
					cep,
					estado,
					cidade,
					bairro,
					logradouro,
					numero,
					complemento,
					parente_nome,
					parente_parentesco,
					parente_celular,
					cad_origem_id,
					tipo,
					senha,
					dt_create,
                    status
                ) VALUES (
                    :uniqid,
					:nome,
					:documento,
					:email,
					:celular,
					:cad_cargo_id,
					:dt_nascimento,
					:cep,
					:estado,
					:cidade,
					:logradouro,
					:bairro,
					:numero,
					:complemento,
					:parente_nome,
					:parente_parentesco,
					:parente_celular,
					:cad_origem_id,
					:tipo,
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
				UPDATE cad_usuarios SET
				imagem = :imagem
				WHERE
				id = :id
			";

			$conn->prepare($sql_update)->execute($dados);
		}

		if (!empty($f_objetivos)) {
			$sql_delete_objetivos = "DELETE FROM usuarios_has_objetivos WHERE cad_usuario_id = :cad_usuario_id";
			$stmt = $conn->prepare($sql_delete_objetivos);
			$stmt->execute(['cad_usuario_id' => $lastInsertId]);

			foreach ($f_objetivos as $objetivo) {
				$dados_objetivos = array(
					"cad_usuario_id" => $lastInsertId,
					"cad_objetivo_id" => $objetivo,
				);
				$sql_insert_objetivos = "
                    INSERT INTO usuarios_has_objetivos (
                        cad_usuario_id,
                        cad_objetivo_id
                    ) VALUES (
                        :cad_usuario_id,
                        :cad_objetivo_id
                    )";
				$stmt = $conn->prepare($sql_insert_objetivos);
				$stmt->execute($dados_objetivos);
			}
		}

		if (!empty($f_informacoes_saude)) {
			$sql_delete_informacoes_saude = "DELETE FROM usuarios_has_informacoes_saude WHERE cad_usuario_id = :cad_usuario_id";
			$stmt = $conn->prepare($sql_delete_informacoes_saude);
			$stmt->execute(['cad_usuario_id' => $lastInsertId]);

			foreach ($f_informacoes_saude as $informacoes_saude) {
				$dados_informacoes_saude = array(
					"cad_usuario_id" => $lastInsertId,
					"cad_informacao_saude_id" => $informacoes_saude,
				);
				$sql_insert_informacoes_saude = "
                    INSERT INTO usuarios_has_informacoes_saude (
                        cad_usuario_id,
                        cad_informacao_saude_id
                    ) VALUES (
                        :cad_usuario_id,
                        :cad_informacao_saude_id
                    )";
				$stmt = $conn->prepare($sql_insert_informacoes_saude);
				$stmt->execute($dados_informacoes_saude);
			}
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
