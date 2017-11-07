<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;  
use SEOMeta;
use OpenGraph;
use Twitter;
use App\KeranjangBelanja; 
use Jenssegers\Agent\Agent;
use Auth;
use DB;

class KeranjangBelanjaController extends Controller
{
    //
	public function daftar_belanja()
	{
		SEOMeta::setTitle('War-Mart.id');
		SEOMeta::setDescription('Warmart marketplace warung muslim pertama di Indonesia');
		SEOMeta::setCanonical('https://war-mart.id');
		SEOMeta::addKeyword(['warmart', 'warung', 'marketplace','toko online','belanja','lazada']);

		OpenGraph::setDescription('Warmart marketplace warung muslim pertama di Indonesia');
		OpenGraph::setTitle('War-Mart.id');
		OpenGraph::setUrl('https://war-mart.id');
		OpenGraph::addProperty('type', 'articles'); 

		$agent = new Agent();
		
		$keranjang_belanjaan = KeranjangBelanja::with(['produk','pelanggan'])->get();
		$cek_belanjaan = $keranjang_belanjaan->count();  

		$jumlah_produk = KeranjangBelanja::select([DB::raw('IFNULL(SUM(jumlah_produk),0) as total_produk')])->first(); 
		$jumlah_produk = KeranjangBelanja::select([DB::raw('IFNULL(SUM(jumlah_produk),0) as total_produk')])->first(); 

      	//MEANMPILKAN PRODUK BELANJAAN
		$produk_belanjaan = '';
		$subtotal = 0;
		foreach ($keranjang_belanjaan as $keranjang_belanjaans) {  
			$harga_produk = $keranjang_belanjaans->produk->harga_jual * $keranjang_belanjaans->jumlah_produk;

			$produk_belanjaan .= '
			<tr class="card" style="margin-bottom: 3px;margin-top: 3px;width: 725px;">
			<td>
			<div class="img-container"> ';
			if ($keranjang_belanjaans->produk->foto != NULL) {
				$produk_belanjaan .= '<img src="foto_produk/'.$keranjang_belanjaans->produk->foto.'">';
			}
			else{
				$produk_belanjaan .= '<img src="image/foto_default.png">';
			}
			$produk_belanjaan .= '
			</div>
			</td>
			<td class="td-name">
			<a href="#jacket">'. $keranjang_belanjaans->produk->nama_barang .'</a>
			<br />
			<small><i class="material-icons">store</i>  '. $keranjang_belanjaans->produk->warung->name .' </small>
			</td>  
			<td class="td-number">
			<b>Rp. '. number_format($harga_produk,0,',','.') .'</b>
			</td> 
			<td class="td-number">
			<div class="btn-group">
			<a href=" '. url('/keranjang-belanja/kurang-jumlah-produk-keranjang-belanja/'.$keranjang_belanjaans->id_keranjang_belanja.''). '" class="btn btn-round btn-info btn-xs"  style="background-color: #f44336"> <i class="material-icons">remove</i> </a>
			<a class="btn btn-round btn-info btn-xs"  style="background-color: #f44336">'. $keranjang_belanjaans->jumlah_produk .' </a>
			<a href=" '. url('/keranjang-belanja/tambah-jumlah-produk-keranjang-belanja/'.$keranjang_belanjaans->id_keranjang_belanja.''). '" class="btn btn-round btn-info btn-xs"  style="background-color: #f44336"> <i class="material-icons">add</i> </a>
			</div>
			</td>   
			<td class="td-actions">
			<a id="btnHapusgProduk" href=" '. url('/keranjang-belanja/hapus-produk-keranjang-belanja/'.$keranjang_belanjaans->id_keranjang_belanja.''). '" type="button" rel="tooltip" data-placement="left" title="Remove item" class="btn btn-simple">
			<i class="material-icons">close</i>
			</a>
			</td>
			</tr>  
			';
			$subtotal = $subtotal += $harga_produk;


		}

		return view('layouts.keranjang_belanja',['keranjang_belanjaan'=>$keranjang_belanjaan,'cek_belanjaan'=>$cek_belanjaan,'agent'=>$agent,'produk_belanjaan'=>$produk_belanjaan,'jumlah_produk'=>$jumlah_produk,'subtotal'=>number_format($subtotal,0,',','.')]);

	}

	public function hapus_produk_keranjang_belanjaan($id)
	{

        // jika gagal hapus
		if (!KeranjangBelanja::destroy($id)) {
			return redirect()->back();
		}
		else{ 
			return redirect()->back();
		}
	}

	public function tambah_jumlah_produk_keranjang_belanjaan($id)
	{
		$produk = KeranjangBelanja::find($id); 
		$produk->jumlah_produk += 1;
		$produk->save();

		return redirect()->back();
	}

	public function kurang_jumlah_produk_keranjang_belanjaan($id)
	{
		$produk = KeranjangBelanja::find($id); 
		$produk->jumlah_produk -= 1;
		$produk->save();
		
		return redirect()->back();
		
	}

	public function tambah_produk_keranjang_belanjaan($id)
	{
		$produk = KeranjangBelanja::create(); 
		$produk->id_produk = $id;
		$produk->id_pelanggan = Auth::user()->id;
		$produk->jumlah_produk += 1;
		$produk->save();
		
		return redirect()->route('keranjang_belanja.daftar_belanja');
		
	}
}
