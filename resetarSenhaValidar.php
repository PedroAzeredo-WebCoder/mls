<?php
require_once("./inc/common.php");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$f_usuario = getParam("f_usuario");
$email = getParam("email");
$token = getParam("token");
$f_senha = getParam("f_senha");
$f_confirmar_senha = getParam("f_confirmar_senha");

try {
    if (!empty($email) && !empty($token)) {
        $query = new sqlQuery();
        $query->addTable("resetar_senha");
        $query->addcolumn("cad_usuario_id");
        $query->addWhere("reset_token", "=", "'" . $token . "'");
        $query->addWhere("reset_token_expiration", ">=", "NOW()");
        $query->setLimit(1);

        if ($conn->query($query->getSQL()) && getDbValue($query->getCount()) != 0) {
            foreach ($conn->query($query->getSQL()) as $row) {
                if (!empty($f_senha) && !empty($f_confirmar_senha) && !empty($row["cad_usuario_id"])) {
                    $dados_pass = array(
                        "id" => $row["cad_usuario_id"],
                        "senha" => validar_senha($f_senha, $f_confirmar_senha),
                    );

                    $sql_update_pass = "UPDATE cad_usuarios SET senha = :senha WHERE id = :id";

                    $stmt = $conn->prepare($sql_update_pass);
                    $stmt->execute($dados_pass);
                    setAlert("Alteração efetuada com sucesso!", "success");
                    redirect("login.php");
                }
            }
        }
    } else {
        $query = new sqlQuery();
        $query->addTable("cad_usuarios");
        $query->addcolumn("id");
        $query->addcolumn("email");
        $query->addWhere("email", "=", "'" . $f_usuario . "'");
        $query->addWhere("status", "=", 1);
        $query->setLimit(1);

        $rowCount = getDbValue($query->getCount());

        if ($rowCount == 0) {
            throw new Exception("Usuário não encontrado, tente novamente.");
        }

        foreach ($conn->query($query->getSQL()) as $row) {
            $cad_usuario_id = $row["id"];
        }

        validar_email($f_usuario);

        $token = generateToken();

        $token_expiration = time() + (24 * 60 * 60);

        $emailSent = sendResetEmail($f_usuario, $token);

        if ($emailSent) {

            $dados = array(
                "cad_usuario_id" => $cad_usuario_id,
                "reset_token" => $token,
                "reset_token_expiration" => date('Y-m-d H:i:s', $token_expiration),
            );

            $sql_insert = "
                INSERT INTO resetar_senha (
                    cad_usuario_id, 
                    reset_token,
                    reset_token_expiration,
                    dt_create
                ) VALUES (
                    :cad_usuario_id, 
                    :reset_token,
                    :reset_token_expiration,
                    NOW()
                )";

            $stmt = $conn->prepare($sql_insert);
            $stmt->execute($dados);
            $lastInsertId = $conn->lastInsertId();

            setAlert("Redefinição enviada!", "success");
            redirect("login.php");
        } else {
            throw new Exception("Ocorreu um erro ao enviar o e-mail de redefinição, tente novamente mais tarde");
        }
    }
} catch (Exception $e) {
    writeLogs("==== " . __FILE__ . " ====", "error");
    writeLogs("Action: Login SQL", "error");
    writeLogs(print_r($e, true), "error");
    writeLogs(printSQL($query, NULL, true), "error");
    setAlert($e->getMessage(), "error");
    redirect("resetarSenha.php");
}
