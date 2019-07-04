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

$data = $grabber->getData('laptop asus');

// $data = $grabber->getData('laptop asus');
// print_r($data);