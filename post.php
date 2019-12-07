<?php
ini_set ( 'max_execution_time',-1); 
ini_set ( 'memory_limit',-1); 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
/*
PANGGIL FILE CLASS
*/
require 'vendor/autoload.php';
include_once 'App/Grabber.class.php';
include_once 'App/Fungsi.class.php';
/*
DEKLARASIKAN CLASS
*/
$grabber = new App\Grabber();
$fungsi = new App\Fungsi();



if(isset($_POST))
{
	$grabber->saveUlasan($_POST);
	echo json_encode($_POST);
}



// $data = $grabber->GetDetailTokopedia('https://www.tokopedia.com/zoemi88/laptop-asus-murah?trkid=f=Ca0000L000P0W0S0Sh,Co0Po0Fr0Cb0_src=catalog_page=1_ob=23_q=_po=2_catid=289');
// print_r($data);