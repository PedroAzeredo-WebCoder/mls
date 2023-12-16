<?php
require_once("./inc/common.php");

writeLogs("==== " . __FILE__ . " ====", "access");
writeLogs(print_r($_POST, true), "access");

$f_usuario       = getParam("f_usuario");
$f_senha         = md5(getParam("f_senha"));

$query = new sqlQuery();
$query->addTable("cad_usuarios");
$query->addcolumn("id");
$query->addcolumn("uniqid");
$query->addcolumn("email");
$query->addWhere("email", "=", "'" . $f_usuario . "'");
$query->addWhere("senha", "=", "'" . $f_senha . "'");
$query->addWhere("status", "=", 1);
$query->setLimit(1);

try {
    $rowCount = getDbValue($query->getCount());

    if ($rowCount == 0) {
        throw new Exception("Erro ao logar, tente novamente");
    }

    foreach ($conn->query($query->getSQL()) as $row) {

        $dados = array(
            "cad_usuario_id" => $row["id"],
            "ip"             => $_SERVER['REMOTE_ADDR']
        );

        $sql_insert = "
        INSERT INTO log_acessos_usuarios (
            cad_usuario_id,
            data,
            ip
        ) VALUES (
            :cad_usuario_id,
            NOW(),
            :ip
        )";

        $expiration = time() + (86400 * 5);
        setcookie('uniqid', $row["uniqid"], $expiration, '/');
        setcookie('email', $row["email"], $expiration, '/');

        try {
            $conn->prepare($sql_insert)->execute($dados);
            $lastInsertId = $conn->lastInsertId();
        } catch (PDOException $e) {
            writeLogs("==== " . __FILE__ . " ====", "error");
            writeLogs("Action: Insert SQL", "error");
            writeLogs(print_r($e, true), "error");
            writeLogs(printSQL($sql_insert, $dados, true), "error");
        }
        setSession('SYSGER', $row["uniqid"]);
    }

    redirect("index.php");
} catch (Exception $e) {
    writeLogs("==== " . __FILE__ . " ====", "error");
    writeLogs("Action: Login SQL", "error");
    writeLogs(print_r($e, true), "error");
    writeLogs(printSQL($query, NULL, true), "error");
    setAlert($e->getMessage(), "error");
    redirect("login.php");
}
