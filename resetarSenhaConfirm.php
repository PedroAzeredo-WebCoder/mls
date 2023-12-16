<?php

require_once("./inc/common.php");

$form = new Form("resetarSenhaValidar.php");
$form->addField(hiddenField(getParam("email"), "email"));
$form->addField(hiddenField(getParam("token"), "token"));
$form->addField(passField("Senha", NULL, NULL, true));
$form->addField(passField("Confirmar Senha", NULL, NULL, true));
$form->addField(submitBtn("Enviar"));

$template = new Template("Login");
$template->setTemplate("login");
$template->addContent($form->writeHtml());
$template->writeHtml();
