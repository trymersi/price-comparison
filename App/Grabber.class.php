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
		$this->connect = mysqli_connect("localhost", "root", "", "ranti_skripsi");
	}


	public function getData($kw)
	{
		$kw = $this->fungsi->BersihKW($kw);
		$cekData = mysqli_query($this->connect,"select * from keyword where keyword like '%".$kw."%'");
		$hitungData = mysqli_num_rows($cekData);
		
			$simpanKw = mysqli_query($this->connect,"insert into keyword (keyword) values('".$kw."')");
			$tokopedia = $this->GetSearchTokopedia($kw);
			$bukalapak = $this->GetSearchBukalapak($kw);
			$blibli = $this->getSearchBibli($kw);
			$jdid = $this->GetSearchJdid($kw);

			$this->saveDataProduk($this->connect->insert_id,$tokopedia);
			$this->saveDataProduk($this->connect->insert_id,$bukalapak);
			$this->saveDataProduk($this->connect->insert_id,$blibli);
			$this->saveDataProduk($this->connect->insert_id,$jdid);
		
	}

	public function saveDataProduk($idKw,$ar)
	{

			foreach($ar as $key)
			{
				if($key['nama_related'] =='')
				{
					continue;
				}
				$simpan = mysqli_query($this->connect,"insert into data_produk values('".$key['url']."','".$idKw."','".$key['kode_toko']."','".$key['nama_produk']."','".$key['gambar']."','".$key['harga']."','".$key['rating']."','".$key['ulasan']."','".$key['kondisi']."','".$key['kategori_barang']."','".$key['diskon']."','".$key['kode_produk']."','".$key['nama_related']."','".$key['sku']."')");
			}
			
		
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
						$produk[$i]['kode_produk'] = $key['id'];
						$produk[$i]['sku'] = $key['sku'];
						$produk[$i]['kode_toko'] = '1';
						$produk[$i]['url'] = base64_encode($produk[$i]['nama_produk']);
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
		if($data['http_code'] == 200)
		{
			$data = json_decode($data['content'],true);
			if(count($data['products']) != 0 )
			{
				$i = 0;
				foreach($data['products'] as $key)
				{
					$produk[$i]['kode_produk'] = $key['id'];
					$produk[$i]['nama_produk'] = $key['name'];
					$produk[$i]['sku'] = '';
					$produk[$i]['url'] = base64_encode($key['url']);
					$produk[$i]['kode_toko'] = '2';
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
		}
		return $produk;
	}

	public function GetSearchLazada($kw)
	{
		$kw = $this->fungsi->BersihKW($kw);
		$url = "https://www.lazada.co.id/catalog/?q=".$kw."&_keyori=ss&from=input&spm=a2o4j.searchlist.search.go.4c3d6cf0OYOxLh";
		$data = $this->fungsi->getData($url);
		print_r($data);


	}


	public function getSearchBibli($kw)
	{
		$produk = array();
		$kw = $this->fungsi->BersihKW($kw);
		$url = "https://www.blibli.com/backend/search/products?page=1&start=0&searchTerm=".$kw."&intent=true&merchantSearch=true&customUrl=&sort=0&showFacet=true";
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			if($data['content'])
			{
				$data = json_decode($data['content'],true);
				
				$i=0;
				foreach($data['data']['products'] as $key)
				{
					$produk[$i]['kode_produk'] = $key['id'];
					$produk[$i]['sku'] = $key['sku'];
					$produk[$i]['kode_toko'] = '3';
					$produk[$i]['nama_produk'] = $key['name'];
					$produk[$i]['url'] = base64_encode("https://www.blibli.com".$key['url']);
					$produk[$i]['gambar'] = $key['images'][0];
					$produk[$i]['harga'] = $this->fungsi->BersihRp($key['price']['priceDisplay']);
					$produk[$i]['rating'] = $key['review']['rating'];
					$produk[$i]['ulasan'] = $key['review']['count'];
					$produk[$i]['kondisi'] = 0;
					$produk[$i]['kategori_barang'] = $key['rootCategory']['name'];
					$produk[$i]['diskon'] = $key['price']['discount'];
					$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key['name']);
					$i++;
				}
				
			}
		}
		return $produk;
	}

	public function GetSearchJdid($kw)
	{
		$produk = array();
		$kw = $this->fungsi->BersihKW($kw);
		$url = "https://www.jd.id/search?keywords=".$kw;
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$i = 0;
			$dom = $this->dom->load($url);
			foreach($dom->find('.list-products-t ul li') as $key)
			{
				$produk[$i]['kode_produk'] ='';
				$produk[$i]['sku'] = '';
				$produk[$i]['kode_toko'] = '4';
				$produk[$i]['nama_produk'] = $key->find('.p-desc a',0)->title;
				$produk[$i]['url'] = base64_encode($key->find('.p-desc a',0)->href);
				$produk[$i]['gambar'] = $key->find('.p-pic img',0)->src;
				$produk[$i]['harga'] = $this->fungsi->BersihRp(strip_tags(trim($key->find('.p-price span',0))));
				$produk[$i]['rating'] = '4.5';
				$produk[$i]['ulasan'] =  $this->fungsi->bersihKurung($key->find('.p-user-line .p-comstar .p-comstar-span',0));
				$produk[$i]['kondisi'] = 0;
				$produk[$i]['kategori_barang'] = 0;
				$produk[$i]['diskon'] = str_replace(' % OFF','',strip_tags(trim($key->find('.p-price span',1))));
				$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($produk[$i]['nama_produk']);
				$i++;
			}
		}
		return $produk;
	}

	public function GetDetailTokopedia($url)
	{
		$produk = array();
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$dom = $this->dom->load($url);
			$produk['harga'] = $dom->find('input[id=product_price_int]',0)->value;
			$produk['product_id'] =  $dom->find('input[name=product_id]',0)->value;
			foreach($dom->find('.container-product .rvm-left-column') as $key)
			{
				$produk['nama_produk'] = strip_tags($key->find('.rvm-left-column--right h1',0));
				$produk['url'] = $url;
				$i = 0;

				$produk['deskripsi'] = $key->find('.product-summary #info',0)->innerHtml;

				foreach($key->find('.rvm-left-column--left .product-detail__img-holder .content-img img') as $img)
				{
					$produk['image'][$i] = $img->src;
					$i++;
				}

			}
		}
#product-309570958 > div > div.rvm-left-column > div.product-summary
		/*
			sesi pengambilan data rating dan ulasan
		*/
		$url = "https://www.tokopedia.com/reputationapp/review/api/v1/rating?product_id=".$produk['product_id'];
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$data = json_decode($data['content'],true);
			$produk['rating'] = $data['data']['rating_score'];
			$produk['ulasan'] = $data['data']['total_review'];
			
		}

		/*
			sesi pengambilan data ulasan detail
		*/

		$url = "https://www.tokopedia.com/reputationapp/review/api/v2/product/".$produk['product_id']."?page=1&total=15";
		$data = $this->fungsi->getData($url);
		$i = 0;
		if($data['http_code'] == 200)
		{
			$data = json_decode($data['content'],true);
			$data2 = $data['data'];
			foreach($data2['list'] as $key)
			{
				$produk['ulasan_detail'][$i]['pesan'] = $key['message'];
				$produk['ulasan_detail'][$i]['rating'] = $key['rating'];
				$produk['ulasan_detail'][$i]['waktu'] = $key['time_format']['date_time_fmt1'];
				$produk['ulasan_detail'][$i]['nama'] = $key['reviewer']['full_name'];
				$produk['ulasan_detail'][$i]['foto'] = $key['reviewer']['profile_picture'];
				$i++;
			}
		}	
		return $produk;
	}

	public function GetDetailBukalapak($id)
	{
		$produk = array();
		$url = 'https://api.bukalapak.com/v2/products/'.$id.'.json';
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$data = json_decode($data['content'],true);
			$produk['harga'] = $data['product']['price'];
			$produk['product_id'] = $id;
			$produk['nama_produk'] = $data['product']['name'];
			$produk['url'] = $data['product']['url'];
			$produk['deskripsi'] = $data['product']['desc'];
			$produk['image'] = $data['product']['images'];
			$produk['rating'] = $data['product']['rating']['average_rate'];
			$produk['ulasan'] = $data['product']['rating']['user_count'];

			
		}
		print_r($produk);
	}

	public function GetDetailBlibli($url)
	{
		$pecah = explode('/',$url);
		$hitung = count($pecah);
		$pecah = $pecah[$hitung-1];
		$pecah = explode('?ds=',$pecah);
		$kodedepan = $pecah[0];
		$pecah = explode('&',$pecah[1]);
		$kodebelakang = $pecah[0];
		$produk = array();
		$url = "https://www.blibli.com/backend/product/products/".$kodedepan."/_summary?selectedItemSku=".$kodebelakang;
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$data = json_decode($data['content'],true);
			$data = $data['data'];
			$produk['harga'] = 0;
			$produk['product_id'] = $kodebelakang;
			$produk['nama_produk'] = $data['name'];
			$produk['url'] = $data['url'];
			$produk['deskripsi'] = $data['description'];
			$produk['image'] = $data['images'];
			$produk['rating'] = $data['review']['rating'];
			$produk['ulasan'] = $data['review']['count'];
		}

		return $produk;
	}

	public function GetDetaiJdid($url)
	{
		$produk = array();
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$dom = $this->dom->load($url);
			foreach($dom->find('.main-content') as $key)
			{
				$produk['nama_produk'] = strip_tags($key->find('#summary h1 span',1));
				foreach($key->find('#summary .item-list') as $prod)
				{
					echo $prod;
				}
			}
		}
		//return $produk;
	}
}