<?php

require_once("./inc/common.php");

$form = new Form("loginValidar.php");
$form->addField(emailField("E-mail", NULL, "f_usuario", true));
$form->addField(passField("Senha", NULL, NULL, true));
$form->addField('
<div class="d-flex justify-content-end">
    <a href="resetarSenha.php" class="btn btn-link btn-sm pe-0">Esqueceu sua senha?</a>
</div>
');
$form->addField(submitBtn("Acessar"));

$template = new Template("Login");
$template->setTemplate("login");
$template->addContent($form->writeHtml());
$template->writeHtml();
