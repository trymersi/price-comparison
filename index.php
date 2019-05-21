<?php
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

$data = $grabber->GetSearchBukalapak('motherboard asus x470 am4');
print_r($data);