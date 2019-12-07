<?php
namespace App;

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use App\Fungsi;
class Grabber
{
	function __construct()
	{
		$this->dom = new Dom;
		$this->fungsi = new Fungsi;
		$this->connect = mysqli_connect("localhost", "ranti", "10011994", "ranti");		
		$this->limit = 10;
	}




	public function getData($kw,$toko='',$filter='')
	{

		$kw = $this->fungsi->BersihKW($kw);
		$cekData = mysqli_query($this->connect,"select * from keyword where keyword like '%".$kw."%'");
		$hitungData = mysqli_num_rows($cekData);
		
		//return $elevenia = $this->GetSearchElevenia($kw);

		if($filter == '1')
		{
			$f = 'order by data_produk.harga ASC';
		}
		elseif($filter == '2')
		{
			$f = 'order by data_produk.harga DESC';
		}
		elseif($filter == '3')
		{
			$f = 'order by data_produk.rating DESC';
		}
		elseif($filter == '4')
		{
			$f = 'order by data_produk.ulasan DESC';
		}
		elseif($filter == '5')
		{
			$f = 'order by data_produk.diskon DESC';
		}
		else
		{
			$f = '';
		}

		if($toko == 'undefined')
		{
			$toko='';
		}
		
		if($hitungData == 0)
		{
			$simpanKw = mysqli_query($this->connect,"insert into keyword (keyword) values('".$kw."')");
			$idKeyword = $this->connect->insert_id;
			$tokopedia = $this->GetSearchTokopedia($kw);
			$bukalapak = $this->GetSearchBukalapak($kw);
			$blibli = $this->getSearchBibli($kw);
			$jdid = $this->GetSearchJdid($kw);
			$elevenia = $this->GetSearchElevenia($kw);

			$this->saveDataProduk($idKeyword,$tokopedia);
			$this->saveDataProduk($idKeyword,$bukalapak);
			$this->saveDataProduk($idKeyword,$blibli);
			$this->saveDataProduk($idKeyword,$jdid);
			$this->saveDataProduk($idKeyword,$elevenia);

			if($toko != '')
			{
				$get = mysqli_query($this->connect,"select * from keyword left join data_produk on keyword.id = data_produk.id_keyword where keyword.keyword like '%".$kw."%' AND data_produk.kode_toko='".$toko."' ".$f);
			}
			else
			{
				$get = mysqli_query($this->connect,"select * from keyword left join data_produk on keyword.id = data_produk.id_keyword where keyword.keyword like '%".$kw."%' ".$f);
			}
			
			$row = array();
			while($r = mysqli_fetch_assoc($get))
			{
				$row[] = $r;
			}
			return $row;
		}
		else
		{
			if($toko !='')
			{
				$get = mysqli_query($this->connect,"select data_produk.*,keyword.*,ulasan.ulasan as commentulasan from keyword left join data_produk on keyword.id = data_produk.id_keyword left join ulasan on data_produk.id=ulasan.id where keyword.keyword like '%".$kw."%' AND data_produk.kode_toko='".$toko."' ".$f);
			}
			else
			{
				$get = mysqli_query($this->connect,"select data_produk.*,keyword.*,ulasan.ulasan as commentulasan from keyword left join data_produk on keyword.id = data_produk.id_keyword left join ulasan on data_produk.id=ulasan.id where keyword.keyword like '%".$kw."%' group by data_produk.id ".$f);
			}

			$row = array();
			while($r = mysqli_fetch_assoc($get))
			{
				$row[] = $r;
			}
			return $row;
		}
			
		
	}


	public function getDetail($id,$toko,$kw,$filter='')
	{	
		$cekData = mysqli_query($this->connect,"select * from data_produk where id_url like '%".$id."%'");
		$r = mysqli_fetch_array($cekData);
		$kodeId = $r['id'];
		$ar = array();
		$url = base64_decode($id);
		//return $this->GetDetailElevenia($url);
		//return $url;
		if($toko == 1)
		{	
			$cekData = mysqli_query($this->connect,"select * from produk_detail where id='".$kodeId."'");
			$cek = mysqli_num_rows($cekData);
			
			if($cek == 0 )
			{	
				
				$url = base64_decode($id);
				$get = $this->GetDetailTokopedia($url);
				
				$this->saveDataDetailProduk($kodeId,$get);
				$getData = mysqli_query($this->connect,"select * from data_produk left join produk_detail on data_produk.id=produk_detail.id where data_produk.id_url='".$id."'"); 
				$getImage = mysqli_query($this->connect,"select * from gambar_produk where id='".$kodeId."'");
				$ar['data'] = mysqli_fetch_assoc($getData);

				while($img= mysqli_fetch_assoc($getImage))
				{
					$ar['image'][] = $img;
				}
			}
			else
			{
				$ar = $this->getDetailAvailable($kodeId,$id);
				
			}
			
		}
		elseif($toko == 2)
		{	
			$get = mysqli_fetch_array(mysqli_query($this->connect,"select * from data_produk where kode_panggil='".$id."'"));
			$kodeId = $get['id'];
			$url = $get['id_url'];
			$cekData = mysqli_query($this->connect,"select * from produk_detail where id='".$kodeId."'");
			$cek = mysqli_num_rows($cekData);
			if($cek == 0 )
			{	
				
				//$url = base64_decode($id);
				$get = $this->GetDetailBukalapak($id);
				$this->saveDataDetailProduk($kodeId,$get);
				$getData = mysqli_query($this->connect,"select * from data_produk left join produk_detail on data_produk.id=produk_detail.id where data_produk.id_url='".$id."'"); 
				$getImage = mysqli_query($this->connect,"select * from gambar_produk where id='".$kodeId."'");
				$ar['data'] = mysqli_fetch_assoc($getData);

				while($img= mysqli_fetch_assoc($getImage))
				{
					$ar['image'][] = $img;
				}
			}
			else
			{
				$ar = $this->getDetailAvailable($kodeId,$url);
			}
			
		}
		elseif($toko == 3)
		{
			$cekData = mysqli_query($this->connect,"select * from produk_detail where id='".$kodeId."'");
			$cek = mysqli_num_rows($cekData);
			
			if($cek == 0 )
			{	
				$url = base64_decode($id);

				$get = $this->GetDetailBlibli($url);
				$this->saveDataDetailProduk($kodeId,$get);
				$getData = mysqli_query($this->connect,"select * from data_produk left join produk_detail on data_produk.id=produk_detail.id where data_produk.id_url='".$id."'"); 
				$getImage = mysqli_query($this->connect,"select * from gambar_produk where id='".$kodeId."'");
				$ar['data'] = mysqli_fetch_assoc($getData);

				while($img= mysqli_fetch_assoc($getImage))
				{
					$ar['image'][] = $img;
				}
			}
			else
			{
				$ar = $this->getDetailAvailable($kodeId,$id);
				
			}
		}

		elseif($toko == 4)
		{
			$cekData = mysqli_query($this->connect,"select * from produk_detail where id='".$kodeId."'");
			$cek = mysqli_num_rows($cekData);
			
			$url = base64_decode($id);
			if($cek == 0 )
			{	
				$get = $this->GetDetaiJdid($url);
				$this->saveDataDetailProduk($kodeId,$get);
				$getData = mysqli_query($this->connect,"select * from data_produk left join produk_detail on data_produk.id=produk_detail.id where data_produk.id_url='".$id."'"); 
				$getImage = mysqli_query($this->connect,"select * from gambar_produk where id='".$kodeId."'");
				$ar['data'] = mysqli_fetch_assoc($getData);

				while($img= mysqli_fetch_assoc($getImage))
				{
					$ar['image'][] = $img;
				}
			}
			else
			{
				$ar = $this->getDetailAvailable($kodeId,$id);
				
			}
		}
		elseif($toko == 5)
		{
			$cekData = mysqli_query($this->connect,"select * from produk_detail where id='".$kodeId."'");
			$cek = mysqli_num_rows($cekData);
			
			if($cek == 0 )
			{	
				$url = base64_decode($id);

				$get = $this->GetDetailElevenia($url);
				$this->saveDataDetailProduk($kodeId,$get);
				$getData = mysqli_query($this->connect,"select * from data_produk left join produk_detail on data_produk.id=produk_detail.id where data_produk.id_url='".$id."'"); 
				$getImage = mysqli_query($this->connect,"select * from gambar_produk where id='".$kodeId."'");
				$ar['data'] = mysqli_fetch_assoc($getData);

				while($img= mysqli_fetch_assoc($getImage))
				{
					$ar['image'][] = $img;
				}
			}
			else
			{
				$ar = $this->getDetailAvailable($kodeId,$id);
				
			}
		}


		$ar['ulasan'] = $this->getUlasan($kodeId);
		$ar['list'] = $this->getData($kw,'',$filter);
		return $ar;
	}
	
	public function getUlasan($id)
	{
		$ar = array();
		$getData = mysqli_query($this->connect,"select * from ulasan where id='".$id."'");
		while($u = mysqli_fetch_assoc($getData))
		{
			$ar[] = $u;
		}
		return $ar;
	}
	public function getDetailAvailable($id,$url)
	{
		$ar = array();
				$image = array();
				$getData = mysqli_query($this->connect,"select * from data_produk left join produk_detail on data_produk.id=produk_detail.id where data_produk.id_url='".$url."'"); 
				$getImage = mysqli_query($this->connect,"select * from gambar_produk where id='".$id."'");
				$ar['data'] = mysqli_fetch_assoc($getData);

				while($img= mysqli_fetch_assoc($getImage))
				{
					$ar['image'][] = $img;
				}
		return $ar;
	}

	public function saveDataProduk($idKw,$ar)
	{
		if(isset($ar))
		{
			foreach($ar as $key)
			{
				
				$simpan = mysqli_query($this->connect,"insert into data_produk (id_url,kode_panggil,id_keyword,kode_toko,nama_produk,gambar,harga,rating,ulasan,kondisi,kategori_barang,diskon,kode_barang,nama_related,sku_produk,nama_toko,kota_toko,jumlah_kurir,stok) values('".$key['url']."','".$key['kode_panggil']."','".$idKw."','".$key['kode_toko']."','".$key['nama_produk']."','".$key['gambar']."','".$key['harga']."','".$key['rating']."','".$key['ulasan']."','".$key['kondisi']."','".$key['kategori_barang']."','".$key['diskon']."','".$key['kode_produk']."','".$key['nama_related']."','".$key['sku']."','".$key['nama_toko']."','".$key['kota_toko']."','".$key['jumlah_kurir']."','".$key['stok']."')");
			}
		}
			
			
		
	}


	public function saveDataDetailProduk($id,$ar)
	{
		
		$saveDeskripsi = mysqli_query($this->connect,"insert into produk_detail values('".$id."','".$ar['deskripsi']."')");

		
			if(isset($ar['image']))
			{
				foreach($ar['image'] as $key)
				{
					$saveImage = mysqli_query($this->connect,"insert into gambar_produk values('".$id."','".$key."')");
				}
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
					$total = 1;
					foreach($data as $key)
					{
						$produk[$i]['nama_produk'] = $key['name'];
						$produk[$i]['kode_produk'] = $key['id'];
						$produk[$i]['sku'] = $key['sku'];
						$produk[$i]['kode_toko'] = '1';
						$produk[$i]['kode_panggil'] = base64_encode($key['url']);
						$produk[$i]['url'] = base64_encode($key['url']);
						$produk[$i]['gambar'] = $key['image_url'];
						$produk[$i]['harga'] = $key['price_int'];
						$produk[$i]['rating'] = $key['rating'];
						$produk[$i]['ulasan'] = $key['count_review'];
						$produk[$i]['kondisi'] = $key['condition'];
						$produk[$i]['kategori_barang'] = $key['category_name'];
						$produk[$i]['diskon'] = $key['discount_percentage'];
						$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key['name']);
						$produk[$i]['nama_toko'] = $key['shop']['name'];
						$produk[$i]['kota_toko'] = $key['shop']['location'];
						$produk[$i]['jumlah_kurir'] = $key['courier_count'];
						$produk[$i]['stok'] = $key['stock'];
						$i++;
						
						if($this->limit == $total)
						{
							break;
						}
						$total++;
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
		//$url = "https://api.bukalapak.com/v2/products.json?keywords=".$kw."&conditions\[\]=new";
		$url = "https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&search_source=omnisearch_organic&search%5Bhashtag%5D=&search%5Bkeywords%5D=".$kw;
		$data = $this->fungsi->getData($url);
		$dom = $this->dom->load($data['content']);
		$nama = 'data-name';
	    $url = 'data-url';
	    $dataSrc = 'data-src';
	    $produkMedia = '';
	    $i = 1;
	    foreach($dom->find('.product-gallery li  article') as $key)
	    {
	        $produk[$i]['nama_produk'] = $key->$nama;
	        $produk[$i]['kode_produk'] = $this->fungsi->bukalapakGetID($key->$url);
	        $produk[$i]['kode_panggil'] = base64_encode('https://www.bukalapak.com'.$key->$url);
	        $produk[$i]['sku'] = '';
	        $produk[$i]['url'] = base64_encode('https://www.bukalapak.com'.$key->$url);
	        $produk[$i]['kode_toko'] = '2';
	        $produk[$i]['gambar'] = $key->find('.product-media a picture source',0)->$dataSrc;
	        $produk[$i]['kondisi'] = '';
	        $produk[$i]['kategori_barang'] = '';
	        $harga = $key->find('.product-price',0);
	        $produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key->$nama);
	        $produk[$i]['nama_toko'] = strip_tags($key->find('.product-seller .user__name',0));
	        $produk[$i]['kota_toko'] = strip_tags($key->find('.product-seller .user-city',0));
			$produk[$i]['jumlah_kurir'] = 0;
			$produk[$i]['stok'] = 0;
	        //echo $harga."<Br><BR>";
	        if(!empty($harga->find('.product-price__original',0)))
	        {
	        	$produk[$i]['harga'] = str_replace(["Rp","."],'',strip_tags($harga->find('.product-price__reduced',0)));
	        	$hargaBiasa = str_replace(["Rp","."],'',strip_tags($harga->find('.product-price__original',0)));
	        	$hargaDiskon =  str_replace(["Rp","."],'',strip_tags($harga->find('.product-price__reduced',0)));
	        	$diskon = (($hargaBiasa-$hargaDiskon)/$hargaBiasa)*100;
	        	$produk[$i]['diskon'] = number_format($diskon);
	        }
	        else
	        {	$produk[$i]['harga'] = str_replace(["Rp","."],'',strip_tags($harga));
	    		$produk[$i]['diskon'] = '0';
	        	//echo 'tidak';
	        }

	        $rating = $key->find('.product__rating',0);
	        if(!empty($rating->find('.review__aggregate',0)))
	        {
	        	$produk[$i]['rating'] = strip_tags($rating->find('.rating',0));

	        	$produk[$i]['ulasan'] = str_replace(' ulasan','',strip_tags($rating->find('.review__aggregate',0)));
	        }
	        else
	        {
	        	$produk[$i]['rating'] = '';
	        	$produk[$i]['ulasan'] = '';
	        }
	        $i++;
	    }

		return $produk;
	}

	public function getSearchBibli($kw)
	{
		$produk = array();
		$kw = $this->fungsi->BersihKW($kw);
		$url = "https://www.blibli.com/backend/search/products?page=1&start=0&searchTerm=".$kw."&intent=true&merchantSearch=true&customUrl=&sort=0&showFacet=true";
		//$url = 'https://www.blibli.com/backend/search/products?page=1&start=0&searchTerm=asus+f570zd+r5591t+notebook+black+amd&intent=true&merchantSearch=true&customUrl=&sort=0&showFacet=true';
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			if($data['content'])
			{
				$data = json_decode($data['content'],true);
				$total = 1;
				$i=0;
				if(isset($data['data']['correctedSearchResponses'][0]))
				{
					$data = $data['data']['correctedSearchResponses'][0]['products'];

				}
				else
				{
					$data = $data['data']['products'];
				}
				if(count($data))
				{
					foreach($data as $key)
					{
						$produk[$i]['kode_produk'] = $key['id'];
						$produk[$i]['sku'] = $key['sku'];
						$produk[$i]['kode_toko'] = '3';
						$produk[$i]['nama_produk'] = $key['name'];
						$produk[$i]['kode_panggil'] = base64_encode("https://www.blibli.com".$key['url']);
						$produk[$i]['url'] = base64_encode("https://www.blibli.com".$key['url']);
						$produk[$i]['gambar'] = $key['images'][0];
						$produk[$i]['harga'] = $this->fungsi->BersihRp($key['price']['priceDisplay']);
						$produk[$i]['rating'] = $key['review']['rating'];
						$produk[$i]['ulasan'] = $key['review']['count'];
						$produk[$i]['kondisi'] = 0;
						$produk[$i]['kategori_barang'] = $key['rootCategory']['name'];
						$produk[$i]['diskon'] = $key['price']['discount'];
						$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key['name']);
						$produk[$i]['nama_toko'] = $key['brand'];
				        $produk[$i]['kota_toko'] = '-';
						$produk[$i]['jumlah_kurir'] = 0;
						$produk[$i]['stok'] = 0;
						$i++;
						if($this->limit == $total)
						{
							break;
						}
						$total++;
					}
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
			//$dom = $this->dom->load($url);
			$pecah = explode('window.pageModel = ',$data['content']);
			
			$pecah = explode('</script>',$pecah[1]);
			$cek = strpos($pecah[0],"window.crumb");
				if($cek !== FALSE)
				{
					$pecah = explode('window.crumb',$pecah[0]);
				}
			$data = strip_tags(trim($pecah[0]));
			$data = str_replace('};', '}', $data);
			$data = json_decode($data,true);
			$total = 1;
			
			foreach ($data['data']['paragraphs'] as $key) 
			{
				$data = $key['Content'];
				$url = 'https://www.jd.id/product/'.$this->fungsi->BersihKW($data['title'],'-').'_'.$key['spuid'].'/'.$key['skuid'].'.html';
				$produk[$i]['kode_produk'] ='';
				$produk[$i]['sku'] = $key['skuid'];
				$produk[$i]['kode_toko'] = '4';
				$produk[$i]['nama_produk'] = $data['title'];
				$produk[$i]['url'] = base64_encode($url);
				$produk[$i]['kode_panggil'] = base64_encode($url);
				$produk[$i]['harga'] = $this->fungsi->BersihRp($data['price']);
				$produk[$i]['gambar'] = "https://img20.jd.id/Indonesia/s800x800_/".$data['imguri'];
				$produk[$i]['rating'] = $key['commentscore'];
				$produk[$i]['ulasan'] = $key['commentcount'];
				$produk[$i]['kondisi'] = 0;
				$produk[$i]['kategori_barang'] = (isset($data['fr_cid2name']) ? $data['fr_cid2name'] : '-' );
				$produk[$i]['diskon'] = $data['discountRate'];
				$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($data['title']);
				$produk[$i]['nama_toko'] = $data['vendername'];
		        $produk[$i]['kota_toko'] = $data['venderaddress'];
				$produk[$i]['jumlah_kurir'] = 0;
				$produk[$i]['stok'] = $key['stockRemainNum'];
				$i++;
				if($this->limit == $total)
				{
					break;
				}
				$total++;
			}
		}
		return $produk;
	}

	public function GetSearchElevenia($kw)
	{
		$produk = array();
		$kw = $this->fungsi->BersihKW($kw);
		  $url = "http://www.elevenia.co.id/search?q=".$kw."&lCtgrNo=";
		
		$data = $this->fungsi->getData($url);
		$i = 0;
		if($data['http_code'] == 200)
		{
			$url = json_decode($data['content'],true);
    		$url = $url['template'];
			$dom = $this->dom->load($data['content']);
			$total = 1;
			foreach ($dom->find('ul ul li .group') as $key) 
			{	

				$produk[$i]['kode_produk'] ='';
				$produk[$i]['sku'] = '';
				$produk[$i]['kode_toko'] = '5';
				$produk[$i]['nama_produk'] = $key->find('img',0)->alt;	
		        $produk[$i]['url'] = base64_encode($key->find('a',0)->href);	
		        $produk[$i]['kode_panggil'] = base64_encode($key->find('a',0)->href);
		        $produk[$i]['gambar'] = $key->find('img',0)->src;  
		        $produk[$i]['harga'] = $this->fungsi->BersihRp(strip_tags($key->find('.price strong',0)));
		        $produk[$i]['nama_toko'] = strip_tags($key->find('.seller .stroeName a',0));;
		        $produk[$i]['kota_toko'] = strip_tags($key->find('.sellerPlace',0));
				$produk[$i]['jumlah_kurir'] = 0;
				$produk[$i]['stok'] = 0;
		        if($key->find('.rankingArea .rating',0) !== null)
		        {
		            $rat = $key->find('.rankingArea .rating',0);
		            $pecah = explode('class="rating ico_ranking ',$rat);
		            $pecah = explode('">', $pecah[1]);
		            $rat = $pecah[0];
		            if($rat == 'satisLevel5')
		            {
		                $rating = '5';
		            }
		            elseif($rat == 'satisLevel4')
		            {
		                $rating = '4';
		            }
		             elseif($rat == 'satisLevel3')
		            {
		                $rating = '3';
		            }
		            elseif($rat == 'satisLevel2')
		            {
		                $rating = '2';
		            }
		            else
		            {
		                $rating = '1';
		            }
		            $produk[$i]['rating'] = $rating;

		            $ul = strip_tags($key->find('.rankingArea .rating a',0));
		            $pecah = explode('(',$ul);
		            $cek = strpos($pecah[1],' reviews)');
		            if($cek !== FALSE)
		            {
		                $pecah = explode(' reviews)',$pecah[1]);
		            }
		            else
		            {
		                 $pecah = explode(' review)',$pecah[1]);
		            }
		            
		            $produk[$i]['ulasan'] = $pecah[0];

		        }
		        else
		        {
		        	$produk[$i]['ulasan'] = '';
		        	$produk[$i]['rating'] = '';
		        }
		   		$produk[$i]['diskon'] = str_replace('%','',strip_tags($key->find('.price span',0)));
		       	$produk[$i]['nama_related'] = $this->fungsi->filterKeyword($key->find('img',0)->alt);
		       	$produk[$i]['kondisi'] = 0;
		       	$produk[$i]['kategori_barang'] = '';
		       	$i++;
		       	if($this->limit == $total)
				{
					break;
				}
				$total++;
			}
		}
		return $produk;
	}

	public function GetDetailTokopedia($url)
	{
		$produk = array();
		//$data = $this->fungsi->getData($url);
		
		
		$data = file_get_contents($url);
			
		$dom = $this->dom->load($data);
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
		
#product-309570958 > div > div.rvm-left-column > div.product-summary
		/*
			sesi pengambilan data rating dan ulasan
		
		
		$url = "https://www.tokopedia.com/reputationapp/review/api/v1/rating?product_id=".$produk['product_id'];
		$data = $this->fungsi->getData($url);
		if($data['http_code'] == 200)
		{
			$data = json_decode($data['content'],true);
			$produk['rating'] = $data['data']['rating_score'];
			$produk['ulasan'] = $data['data']['total_review'];
			
		}

		
			sesi pengambilan data ulasan detail
		

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
		
		*/
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
		return $produk;
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
		//$url = 'https://www.blibli.com/backend/product/products/pc--MTA-2431515/_summary?selectedItemSku=PTP-60102-00019-00001';
		$data = $this->fungsi->getData($url);
		//return print_r($data);
		if($data['http_code'] == 200)
		{
			$cek = json_decode($data['content'],true);

			if(isset($cek['data']))
			{
				$data = json_decode($data['content'],true);
				$data = $data['data'];
				$produk['deskripsi'] = $data['description'];
				foreach($data['images'] as $img)
				{
					$produk['image'][] =  $img['full'];
				}
				
			}
			else
			{
				$dom = $this->dom->load($data['content']);
				foreach ($dom->find('data') as $key) 
				{
					$produk['deskripsi'] =  $key->find('description')->text;
					foreach($key->find('images images') as $img)
					{
						$produk['image'][] =  $img->find('full')->text;
					}
				}
			}
			
		}

		return $produk;
	}

	public function GetDetaiJdid($url)
	{
		//$produk = array();
		//$url = str_replace('//www', 'www', $url);
		$produk = array();
		$data = $this->fungsi->getData($url);
		//print_r($data);
		
		$pecah = explode('additional:',$data['content']);
		$pecah = explode('</script>',$pecah[1]);
		$pecah = explode(',"openChecked":false',$pecah[0]);
	
	
		$data = $pecah[0]."}";
		$data = json_decode($data,true);
		$produk['deskripsi'] = $data['description'];
		
		$i = 0;
		foreach($data['thumbs'] as $key)
		{
			$produk['image'][$i] = "https://img20.jd.id/Indonesia/s800x800_/".$key['imgUrl'];
			$i++;
		}
		
		return $produk;
	}

	public function GetDetailElevenia($url)
	{
		$produk = array();
		$data = $this->fungsi->getData($url);
		$img = 'data-url';
		//return $url;
		if($data['http_code'] == 200)
		{
			$i = 0;
			$dom = $this->dom->load($data['content']);
			
			foreach($dom->find('.thumbs ul li a') as $key)
		    {
		        $produk['image'][$i] =  trim($key->href);
		        $i++;
		    }
		    if(!isset($produk['image']))
		    {
		    	$produk['image'][1] = $dom->find('.figure a',0)->href;	
		    }
		    $produk['deskripsi'] = $dom->find('.detailWrap .compWrap',0);
		}
		return $produk;
	}
	
	public function saveUlasan($data){
		$id = $data['id'];
		$nama = $data['nama'];
		$ulasan = $data['ulasan'];
		
		$getData = mysqli_query($this->connect,"select * from data_produk where id_url='".$id."'"); 
		$id = mysqli_fetch_assoc($getData);
		$id = $id['id'];
		$simpan = mysqli_query($this->connect,"insert into ulasan values('".$id."','".$nama."','".$ulasan."')");
	}

	public function getUlasanList(){
		$get = mysqli_query($this->connect,"select count(ulasan.id) as total_ulasan,data_produk.* from ulasan left join data_produk on ulasan.id=data_produk.id group by ulasan.id");
		$ar = array();
		while($r = mysqli_fetch_assoc($get))
		{
			$ar[] = $r;
		}
		return $ar;
	}

	public function getKw()
	{
		$get = mysqli_query($this->connect,"select * from keyword order by id DESC");
		$ar = array();
		while($r = mysqli_fetch_assoc($get))
		{
			$ar[] = ucwords(str_replace('+',' ',$r['keyword']));
		}
		return $ar;

	}
}