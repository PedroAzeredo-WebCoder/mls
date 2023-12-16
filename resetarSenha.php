<?php

require_once("./inc/common.php");

$form = new Form("resetarSenhaValidar.php");
$form->addField(emailField("E-mail", NULL, "f_usuario", true));
$form->addField(submitBtn("Enviar"));

$template = new Template("Login");
$template->setTemplate("login");
$template->addContent($form->writeHtml());
$template->writeHtml();
