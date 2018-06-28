<?php
require_once("../../API/qqConnectAPI.php");
$qc = new QC();
echo $qc->qq_callback().'<br>';
echo $qc->get_openid().'<br>';

header("Content-Type: text/html; charset=UTF-8");
var_dump($qc->get_user_info());
