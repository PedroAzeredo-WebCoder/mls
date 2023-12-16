<?php

require_once("./inc/common.php");
checkAccess();

$dash = array();
$components = array();

$component = array();
$i = 3;
foreach ($components as $item) {
    $numeroDeItens = $i * 100;
    $component[] = str_replace('numeroDeItens', $numeroDeItens, $item);
    $i++;
}

$dash[] = '
    <section id="dash">
        <div class="row">
           ' . implode("", $component) . '
        </div>
    </section>
';

$template = new Template();
$template->addContent(implode("", $dash));
$template->writeHtml();
