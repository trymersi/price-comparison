<?php
<<<<<<< HEAD
ini_set ( 'max_execution_time',-1); 
ini_set ( 'memory_limit',-1); 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
=======
header("Content-Type: application/json");

>>>>>>> 0d70408a8cb5cd6b28ecbe5e7cadf38dc5fde82e
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

<<<<<<< HEAD

if($_GET['type'] == 'search')
{
	if(isset($_GET['kw']))
	{
		$toko = $_GET['toko'];
		$filter = $_GET['filter'];

		$data = $grabber->getData($_GET['kw'],$toko,$filter);
		echo json_encode($data);
		
	}
}
else
{
	if(isset($_GET['filter']))
	{
		$data = $grabber->getDetail($_GET['id'],$_GET['toko'],$_GET['kw'],$_GET['filter']);
	}
	else
	{
		$data = $grabber->getDetail($_GET['id'],$_GET['toko'],$_GET['kw']);
	}
	
	echo json_encode($data);
}




// $data = $grabber->GetDetailTokopedia('https://www.tokopedia.com/zoemi88/laptop-asus-murah?trkid=f=Ca0000L000P0W0S0Sh,Co0Po0Fr0Cb0_src=catalog_page=1_ob=23_q=_po=2_catid=289');
// print_r($data);
=======
$data = $grabber->GetSearchBukalapak('motherboard asus x470 am4');
print_r($data);
>>>>>>> 0d70408a8cb5cd6b28ecbe5e7cadf38dc5fde82e
