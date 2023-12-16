<?php

require_once("./inc/common.php");

setcookie('uniqid', '', time() - 3600, '/');
setcookie('email', '', time() - 3600, '/');

setSession("SYSGER", "");
redirect("login.php");
