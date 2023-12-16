<?php

require_once("./inc/common.php");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$e = getParam("e", true);
$cad_usuario_id_status = $e["cad_usuario_id_status"];

try {
	if (!empty($cad_usuario_id_status)) {
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
		$f_imagem               = getFileParam("f_imagem");
		$f_nome 				= getParam("f_nome");
		$f_documento            = str_replace(array('.', '-', '/'), array('', '', ''), getParam("f_documento"));
		$f_email                = strtolower(getParam("f_email"));
		$f_celular             	= getParam("f_celular");
		$cad_cargo_id           = getParam("f_cargo");
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
		$f_especialidades		= getParam("f_especialidades");
		$f_senha                = getParam("f_senha");
		$f_confirmar_senha      = getParam("f_confirmar_senha");
		$f_ativo 				= getParam("f_ativo") == "on" ? "1" : "0";

		$cad_local_id           = getParam("f_local");

		$horarios_semana = array();

		for ($i = 1; $i <= 6; $i++) {
			$horario = getParam(strtolower(slug(br_DiaSemana($i)) . "_inicial")) . '|' . getParam(strtolower(slug(br_DiaSemana($i)) . "_final"));
			$horarios_semana[$i] = $horario;
		}

		$dados = array(
			"nome"           => $f_nome,
			"documento"      => $f_documento,
			"email"          => $f_email,
			"celular"        => $f_celular,
			"cad_cargo_id"   => $cad_cargo_id,
			"dt_nascimento"  => $f_dt_nascimento,
			"cep"            => $f_cep,
			"estado"         => $f_estado,
			"cidade"         => $f_cidade,
			"bairro"         => $f_bairro,
			"logradouro"     => $f_logradouro,
			"numero"         => $f_numero,
			"complemento"    => $f_complemento,
			"status"         => $f_ativo,
		);

		for ($i = 1; $i <= 6; $i++) {
			$campo_horario = "horario_" . strtolower(slug(br_DiaSemana($i)));
			if (!empty($horarios_semana[$i])) {
				$dados[$campo_horario] = $horarios_semana[$i];
			}
		}

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
					documento = :documento,
					email = :email,
					celular = :celular,
					cad_cargo_id = :cad_cargo_id,
					cad_local_id = :cad_local_id,
					dt_nascimento = :dt_nascimento,
					cep = :cep,
					estado = :estado,
					cidade = :cidade,
					logradouro = :logradouro,
					bairro = :bairro,
					numero = :numero,
					complemento = :complemento,
					dt_update = NOW()
				";

				for ($i = 1; $i <= 6; $i++) {
					$campo_horario = "horario_" . strtolower(slug(br_DiaSemana($i)));
					$sql_update .= ", " . $campo_horario . " = :" . $campo_horario;
				}

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
			$stmt->execute(['cad_usuario_id' => $cad_cliente_id]);

			foreach ($f_objetivos as $objetivo) {
				$dados_objetivos = array(
					"cad_usuario_id" => $cad_cliente_id,
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
			$stmt->execute(['cad_usuario_id' => $cad_cliente_id]);

			foreach ($f_informacoes_saude as $informacoes_saude) {
				$dados_informacoes_saude = array(
					"cad_usuario_id" => $cad_cliente_id,
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

		if (!empty($f_especialidades)) {
			$sql_delete_especialidades = "DELETE FROM usuarios_has_especialidades WHERE cad_usuario_id = :cad_usuario_id";
			$stmt = $conn->prepare($sql_delete_especialidades);
			$stmt->execute(['cad_usuario_id' => $lastInsertId]);

			foreach ($f_especialidades as $servico) {
				$dados_especialidades = array(
					"cad_usuario_id" => $lastInsertId,
					"cad_servico_id" => $servico,
				);
				$sql_insert_especialidades = "
                    INSERT INTO usuarios_has_especialidades (
                        cad_usuario_id,
                        cad_servico_id
                    ) VALUES (
                        :cad_usuario_id,
                        :cad_servico_id
                    )";
				$stmt = $conn->prepare($sql_insert_especialidades);
				$stmt->execute($dados_especialidades);
			}
		}

		$tipo = 'success';
	}
} catch (PDOException $e) {
	if (!empty($cad_usuario_id_delete)) {
		$actionText = "Erro ao excluir";
	} else if (!empty($cad_usuario_id)) {
		$actionText = "Erro ao alterar";
	}

	$extend = "text: 'Desculpe, ocorreu um erro";
	if (!empty($cad_usuario_id) || !empty($cad_usuario_id_delete)) {
		$extend .= " na ";
		if (!empty($cad_usuario_id)) {
			$extend .= "alteração";
		}
		$extend .= ".";
	}

	$extend .= " Por favor, verifique os campos obrigatórios e/ou os dados inseridos. É possível que alguns dados já tenham sido utilizados.'";
	$tipo = 'error';

	writeLogs("==== " . __FILE__ . " ====", "error");
	if (!empty($cad_usuario_id)) {
		writeLogs("Action: UPDATE SQL", "error");
		writeLogs(printSQL($sql_update, $dados, true), "error");
	}
	writeLogs(print_r($e, true), "error");
}

setAlert($actionText, $tipo, $extend);
redirect(btnLink(["perfil.php", ["cad_usuario_id" => $cad_usuario_id]]));
