<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;  
use SEOMeta;
use OpenGraph;
use Twitter;
use App\Barang; 
use App\KeranjangBelanja; 
use App\KategoriBarang; 
use App\Warung; 
use App\Hpp; 
use App\User; 
use Jenssegers\Agent\Agent;
use Auth;
use DB;

class DetailProdukController extends Controller
{
    // 
	public function listProduk($data_produk){
		$agent = new Agent();

		$daftar_produk = '';
		foreach ($data_produk as $produks) {

			$warung = Warung::select(['name'])->where('id', $produks->id_warung)->first();

			$keranjang_belanjaan = KeranjangBelanja::with(['produk','pelanggan'])->where('id_pelanggan',Auth::user()->id)->where('id_produk',$produks->id)->count(); 

			if ($keranjang_belanjaan == 0) {
				$stok = Hpp::select([DB::raw('IFNULL(SUM(jumlah_masuk),0) - IFNULL(SUM(jumlah_keluar),0) as stok_produk')])->where('id_produk', $produks->id)->where('warung_id', $produks->id_warung)->first();
				$cek_produk = $stok->stok_produk; 
			} else{

				$cek_produk = KeranjangBelanja::where('id_pelanggan',Auth::user()->id)->where('id_produk',$produks->id)->first(); 
				$stok = Hpp::select([DB::raw('IFNULL(SUM(jumlah_masuk),0) - IFNULL(SUM(jumlah_keluar),0) as stok_produk')])->where('id_produk', $cek_produk->id_produk)->where('warung_id', $produks->id_warung)->first();
				$cek_produk = $stok->stok_produk - $cek_produk->jumlah_produk; 

			}   

			$daftar_produk .= '      
			<div class="col-md-3 col-sm-6 col-xs-6 list-produk">
			<div class="card cards card-pricing">
			<a href="'. url('detail-produk/'.$produks->id.''). '">
			<div class="card-image">';
			if ($produks->foto != NULL) {
				$daftar_produk .= '<img src="../foto_produk/'.$produks->foto.'">';
			}
			else{
				$daftar_produk .= '<img src="../image/foto_default.png">';
			}
			$daftar_produk .= '
			</div>
			</a>
			<div class="card-content">
			<div class="footer">  
			<a href="'. url('detail-produk/'.$produks->id.''). '" class="card-title">';
			if (strlen(strip_tags($produks->nama)) <= 33) {
				$daftar_produk .= ''.strip_tags(substr($produks->nama, 0, 60)).'...<br>';
			}
			else{
				$daftar_produk .= ''.strip_tags(substr($produks->nama, 0, 60)).'...';                
			}
			$daftar_produk .= '</a><br>             
			<b style="color:red; font-size:18px"> '.$produks->rupiah.' </b><br>
			<a class="description"><i class="material-icons">store</i>  '.strip_tags(substr($warung->name, 0, 10)).'... </a><br>';

			if ($agent->isMobile()) {
                //JIKA USER LOGIN BUKAN PELANGGAN MAKA TIDAK BISA PESAN PRODUK
				if(Auth::user()->tipe_user == 3){
					if ($cek_produk <= 0) {
						$daftar_produk .= '<a class="btn btn-block"  style="background-color: #01573e" rel="tooltip" title="Stok Tidak Ada" disabled="" ><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>'; 
					}else{
						$daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" class="btn btn-block"  style="background-color: #01573e" rel="tooltip" title="Tambah Ke Keranjang Belanja" id="btnBeliSekarang"><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';						
					}
				}
				else{
					$daftar_produk .= '<button type="button" class="btn btn-block"  style="background-color: #01573e" rel="tooltip" title="Tambah Ke Keranjang Belanja" id="btnBeli"><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';
				}

			}
			else{
                //JIKA USER LOGIN BUKAN PELANGGAN MAKA TIDAK BISA PESAN PRODUK
				if(Auth::user()->tipe_user == 3){
					if ($cek_produk <= 0) {
						$daftar_produk .= '<a class="btn btn-block"  style="background-color: #01573e" rel="tooltip" title="Stok Tidak Ada" disabled="" ><b style="font-size:18px"> Beli Sekarang </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
					}else{
						$daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" id="btnBeliSekarang" class="btn btn-block"  style="background-color: #01573e" rel="tooltip" title="Tambah Ke Keranjang Belanja"><b style="font-size:18px"> Beli Sekarang </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
					}
				}
				else{
					$daftar_produk .= '<button type="button" class="btn btn-block"  style="background-color: #01573e" rel="tooltip" title="Tambah Ke Keranjang Belanja" id="btnBeli"><b style="font-size:18px" > Beli Sekarang</b><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';
				}                
			}
			$daftar_produk .= '
			</div>
			</div>
			</div>
			</div>';
		}
		return $daftar_produk;
	}

	public function detail_produk($id){
		SEOMeta::setTitle('War-Mart.id');
		SEOMeta::setDescription('Warmart marketplace warung muslim pertama di Indonesia');
		SEOMeta::setCanonical('https://war-mart.id');
		SEOMeta::addKeyword(['warmart', 'warung', 'marketplace','toko online','belanja','lazada']);

		OpenGraph::setDescription('Warmart marketplace warung muslim pertama di Indonesia');
		OpenGraph::setTitle('War-Mart.id');
		OpenGraph::setUrl('https://war-mart.id');
		OpenGraph::addProperty('type', 'articles'); 

		$barang = Barang::find($id);   
        //Pilih warung yang sudah dikonfirmasi admin
		$data_warung = User::select(['id_warung'])->where('id_warung', '!=' ,'NULL')->where('konfirmasi_admin', 1)->groupBy('id_warung')->get();
		$array_warung = array();
		foreach ($data_warung as $data_warungs) {
			array_push($array_warung, $data_warungs->id_warung);
		}

		$data_produk = Barang::select(['id','kode_barang', 'kode_barcode', 'nama_barang', 'harga_jual', 'foto', 'deskripsi_produk', 'kategori_barang_id', 'id_warung'])->where('foto', '!=', 'NULL' )->where('kategori_barang_id', $barang->kategori_barang_id )->whereIn('id_warung', $array_warung)->inRandomOrder()->paginate(4);
		$daftar_produk_sama = $this->listProduk($data_produk); 

		$data_produk = Barang::select(['id','kode_barang', 'kode_barcode', 'nama_barang', 'harga_jual', 'foto', 'deskripsi_produk', 'kategori_barang_id', 'id_warung'])->where('foto', '!=', 'NULL' )->where('id_warung', $barang->id_warung )->whereIn('id_warung', $array_warung)->inRandomOrder()->paginate(4);
		$daftar_produk_warung = $this->listProduk($data_produk);             

		$keranjang_belanjaan = KeranjangBelanja::with(['produk','pelanggan'])->where('id_pelanggan',Auth::user()->id)->get();
		$cek_belanjaan = $keranjang_belanjaan->count();   

		if ($keranjang_belanjaan->where('id_produk',$id)->count() == 0) {
			$stok = Hpp::select([DB::raw('IFNULL(SUM(jumlah_masuk),0) - IFNULL(SUM(jumlah_keluar),0) as stok_produk')])->where('id_produk', $id)->where('warung_id', $barang->id_warung)->first();
			$sisa_stok_keluar = $stok->stok_produk; 
		} else{
			
			$cek_produk = KeranjangBelanja::where('id_pelanggan',Auth::user()->id)->where('id_produk',$id)->first(); 
			$stok = Hpp::select([DB::raw('IFNULL(SUM(jumlah_masuk),0) - IFNULL(SUM(jumlah_keluar),0) as stok_produk')])->where('id_produk', $cek_produk->id_produk)->where('warung_id', $barang->id_warung)->first();
			$sisa_stok_keluar = $stok->stok_produk - $cek_produk->jumlah_produk; 

		}   

		$agent = new Agent();
		$logo_warmart = "".asset('/assets/img/examples/warmart_logo.png')."";

		return view('layouts.detail_produk', ['id' => $id, 'barang' => $barang,'cek_belanjaan'=>$cek_belanjaan,'daftar_produk_sama'=>$daftar_produk_sama,'daftar_produk_warung'=>$daftar_produk_warung,'agent'=>$agent,'logo_warmart'=>$logo_warmart,'cek_produk'=>$sisa_stok_keluar]); 

	}
}
