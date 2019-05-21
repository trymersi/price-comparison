<?php
namespace App;
/**
GRABBER CLASS
AUTHOR : TRY MERSIANTO <TRY.MERSIANTO@GMAIL.COM>
*/
require "vendor/autoload.php";
use PHPHtmlParser\dom;
use App\Fungsi;
//include_once ''
class Grabber
{
	function __construct()
	{
		$this->dom = new Dom;
		$this->fungsi = new Fungsi;
	}

	public function GetSearchTokopedia($kw)
	{
		$produk = array();
		$kw = $this->fungsi->BersihKW($kw);
		$url = "https://ace.tokopedia.com/search/product/v3?scheme=https&device=desktop&related=true&source=search&ob=23&st=product&condition=1&user_id=4807191&rows=60&q=".$kw;
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			if($data['content'])
			{
				$data = json_decode($data['content'],true);
				if($data['header']['total_data'] != 0)
				{
					$data = $data['data']['products'];
					$i = 0;
					foreach($data as $key)
					{
						$produk[$i]['nama_produk'] = $key['name'];
						$produk[$i]['url'] = $key['url'];
						$produk[$i]['gambar'] = $key['image_url'];
						$produk[$i]['harga'] = $key['price_int'];
						$produk[$i]['rating'] = $key['rating'];
						$produk[$i]['ulasan'] = $key['count_review'];
						$produk[$i]['kondisi'] = $key['condition'];
						$produk[$i]['kategori_barang'] = $key['category_name'];
						$produk[$i]['diskon'] = $key['discount_percentage'];
						$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key['name']);

						$i++;
					}
					return $produk;
				}
			}
			
		}
	}

	public function GetSearchBukalapak($kw)
	{
		$produk = array();
		$kw = $this->fungsi->BersihKW($kw);
		$url = "https://api.bukalapak.com/v2/products.json?keywords=".$kw."&conditions\[\]=new";
		$data = $this->fungsi->getData($url);
		$data = json_decode($data,true);
		if(count($data['products']) != 0 )
		{
			$i = 0;
			foreach($data['products'] as $key)
			{
				$produk[$i]['nama_produk'] = $key['name'];
				$produk[$i]['url'] = $key['url'];
				$produk[$i]['gambar'] = $key['images'][0];
				$produk[$i]['harga'] = $key['price'];
				$produk[$i]['rating'] = $key['rating']['average_rate'];
				$produk[$i]['ulasan'] = $key['rating']['user_count'];
				$produk[$i]['kondisi'] = $key['condition'];
				$produk[$i]['kategori_barang'] = $key['category_structure'][0];
				$produk[$i]['diskon'] =0;
				$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key['name']);
				$i++;

			}
		}
		return $produk;
	}

	public function GetSearchLazada($kw)
	{

	}
}