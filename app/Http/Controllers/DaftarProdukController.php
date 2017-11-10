<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use App\KategoriBarang;
use App\Warung;
use App\Barang;
use App\User;
use App\KeranjangBelanja; 
use Auth;

class DaftarProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $keranjang_belanjaan = KeranjangBelanja::with(['produk','pelanggan'])->where('id_pelanggan',Auth::user()->id)->get();
      $cek_belanjaan = $keranjang_belanjaan->count();  
      //Pilih warung yang sudah dikonfirmasi admin
      $data_warung = User::select(['id_warung'])->where('id_warung', '!=' ,'NULL')->where('konfirmasi_admin', 1)->groupBy('id_warung')->get();
      $array_warung = array();
      foreach ($data_warung as $data_warungs) {
        array_push($array_warung, $data_warungs->id_warung);
      }

      //PILIH DATA PRODUK
      $data_produk = Barang::select(['id','kode_barang', 'kode_barcode', 'nama_barang', 'harga_jual', 'foto', 'deskripsi_produk', 'kategori_barang_id', 'id_warung'])
      ->inRandomOrder()
      ->whereIn('id_warung', $array_warung)->paginate(12);

      //PILIH DATA KATEGORI PRODUK
      $kategori = KategoriBarang::select(['id','nama_kategori_barang','kategori_icon']);
      //PERINTAH PAGINATION
      $produk_pagination = $data_produk->links();
      //FOTO HEADER
      $foto_latar_belakang = "background-image: url('image/background2.jpg');";
      //FOTO WARMART
      $logo_warmart = "assets/img/examples/warmart_logo.png";
      //TAMPIL DAFTAR PRODUK
      $daftar_produk = $this->listProduk($data_produk);
      //TAMPIL KATEGORI
      $kategori_produk = $this->produkKategori($kategori);
      $nama_kategori = "Temukan Apa Yang Anda Butuhkan";
      //TAMPILAN MOBILE
      $agent = new Agent();

      return view('layouts.daftar_produk', ['kategori_produk' => $kategori_produk, 'daftar_produk' => $daftar_produk, 'produk_pagination' => $produk_pagination, 'foto_latar_belakang' => $foto_latar_belakang, 'nama_kategori' => $nama_kategori, 'agent' => $agent,'cek_belanjaan'=>$cek_belanjaan,'logo_warmart'=>$logo_warmart]);
    }

    public function produkKategori($kategori){
      //Pilih warung yang sudah dikonfirmasi admin
      $data_warung = User::select(['id_warung'])->where('id_warung', '!=' ,'NULL')->where('konfirmasi_admin', 1)->groupBy('id_warung')->get();
      $array_warung = array();
      foreach ($data_warung as $data_warungs) {
        array_push($array_warung, $data_warungs->id_warung);
      }
      //MEANMPILKAN KATEGORI PRODUK
      $kategori_produk = '';
      foreach ($kategori->paginate(4) as $kategori) {
        $jumlah_produk = Barang::where('kategori_barang_id', $kategori->id)->whereIn('id_warung', $array_warung)->count();
        $kategori_produk .= '
        <li>
          <a href="'.route('daftar_produk.filter_kategori',$kategori->id).'" style="color:white"><i class="material-icons">'.$kategori->kategori_icon.'</i>'.$kategori->nama_kategori_barang.' - '.$jumlah_produk.'</a>
        </li>';
      }
      $kategori_produk .= '
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:white"><i class="material-icons">list</i> Lain - Lain <b class="caret"></b></a>
        <ul class="dropdown-menu dropdown-with-icons">';
          foreach ($kategori->get() as $kategori) {
            $jumlah_produk = Barang::where('kategori_barang_id', $kategori->id)->whereIn('id_warung', $array_warung)->count();
            $kategori_produk .= '
            <li>
              <a href="'.route('daftar_produk.filter_kategori',$kategori->id).'"><i class="material-icons">'.$kategori->kategori_icon.'</i>'.$kategori->nama_kategori_barang.' - '.$jumlah_produk.'</a>
            </li>';
          }
          $kategori_produk .= '
        </ul>
      </li>';

      return $kategori_produk;
    }

    public function listProduk($data_produk){
      $agent = new Agent();

      $daftar_produk = '';
      foreach ($data_produk as $produks) {

        $warung = Warung::select(['name'])->where('id', $produks->id_warung)->first();

        $daftar_produk .= '      
        <div class="col-md-3 col-sm-6 col-xs-6 list-produk">
          <div class="card cards card-pricing">
            <a href="'.url("/keranjang-belanja") .'">
              <div class="card-image">';
                if ($produks->foto != NULL) {
                 $daftar_produk .= '<img src="./foto_produk/'.$produks->foto.'">';
               }
               else{
                $daftar_produk .= '<img src="./image/foto_default.png">';
              }
              $daftar_produk .= '
            </div>
          </a>
          <div class="card-content">
            <div class="footer">  
              <a href="'.url("/keranjang-belanja") .'" class="card-title">';
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
                    $daftar_produk .= '<a href="'.url("/keranjang-belanja") .'" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja" id="btnBeliSekarang"><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                  }
                  else{
                    $daftar_produk .= '<button type="button" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja" id="btnBeli"><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';
                  }

                }
                else{
                //JIKA USER LOGIN BUKAN PELANGGAN MAKA TIDAK BISA PESAN PRODUK
                  if(Auth::user()->tipe_user == 3){
                    $daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" id="btnBeliSekarang" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja"><b style="font-size:18px"> Beli Sekarang </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                  }
                  else{
                    $daftar_produk .= '<button type="button" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja" id="btnBeli"><b style="font-size:18px"> Beli Sekarang</b><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';
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

    public function filter_kategori($id)
    {
      $keranjang_belanjaan = KeranjangBelanja::with(['produk','pelanggan'])->where('id_pelanggan',Auth::user()->id)->get();
      $cek_belanjaan = $keranjang_belanjaan->count(); 
  //Pilih warung yang sudah dikonfirmasi admin
      $data_warung = User::select(['id_warung'])->where('id_warung', '!=' ,'NULL')->where('konfirmasi_admin', 1)->groupBy('id_warung')->get();
      $array_warung = array();
      foreach ($data_warung as $data_warungs) {
        array_push($array_warung, $data_warungs->id_warung);
      }

//PILIH PRODUK
      $data_produk = Barang::select(['id','kode_barang', 'kode_barcode', 'nama_barang', 'harga_jual', 'foto', 'deskripsi_produk', 'kategori_barang_id', 'id_warung'])
      ->where('kategori_barang_id', $id)->whereIn('id_warung', $array_warung)->inRandomOrder()->paginate(12);

//PILIH KATEGORI
      $kategori = KategoriBarang::select(['id','nama_kategori_barang','kategori_icon']);
//FOTO HEADER
      $foto_latar_belakang = "background-image: url('../image/background2.jpg');";
//FOTO WARMART
      $logo_warmart = "../assets/img/examples/warmart_logo.png";
//PAGINATION DAFTAR PRODUK
      $produk_pagination = $data_produk->links();
//MENAMPILKAN KATEGORI
      $kategori_produk = $this->produkKategori($kategori);
      $data_kategori = $kategori->first();
      $nama_kategori = "KATEGORI : ".$data_kategori->nama_kategori_barang."";

//TAMPILAN VIA HP
      $agent = new Agent();

      if ($data_produk->count() > 0) {

        $daftar_produk = "";
        foreach ($data_produk as $produks) {
          $warung = Warung::select(['name'])->where('id', $produks->id_warung)->first();

          $daftar_produk .= '      
          <div class="col-md-3 col-sm-6 col-xs-6">
            <div class="card cards card-pricing">
              <a href="'.url("/keranjang-belanja") .'">
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
                <a href="'.url("/keranjang-belanja") .'" class="card-title">';
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
                    $daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" id="btnBeliSekarang" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja"><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                  }
                  else{
                    $daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" id="btnBeliSekarang" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja"><b style="font-size:18px"> Beli Sekarang </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                  }
                  $daftar_produk .= '
                </div>
              </div>
            </div>
          </div>';
        }
      }
      else{
        $daftar_produk = 
        '<div class="col-md-3">
        <div class="card card-product card-plain no-shadow" data-colored-shadow="false">
          <div class="card-image">
            <img src="../image/foto_default.png">
          </div>
          <div class="card-content">
            <a href="#">
              <h4 class="card-title">Tidak Ada Produk</h4>
            </a>
          </div>
        </div>
      </div>';
    }        

    return view('layouts.daftar_produk', ['kategori_produk' => $kategori_produk, 'daftar_produk' => $daftar_produk, 'produk_pagination' => $produk_pagination, 'id' => $id, 'foto_latar_belakang' => $foto_latar_belakang, 'nama_kategori' => $nama_kategori, 'agent' => $agent,'cek_belanjaan'=>$cek_belanjaan,'logo_warmart'=>$logo_warmart]);
  }

  public function pencarian(Request $request){

    $keranjang_belanjaan = KeranjangBelanja::with(['produk','pelanggan'])->where('id_pelanggan',Auth::user()->id)->get();
    $cek_belanjaan = $keranjang_belanjaan->count(); 

//PILIH PRODUK
    $data_produk = Barang::search($request->search)->where('konfirmasi_admin', 1)->paginate(12);
//PILIH KATEGORI
    $kategori = KategoriBarang::select(['id','nama_kategori_barang','kategori_icon']);
//FOTO HEADER
    $foto_latar_belakang = "background-image: url('".asset('/image/background2.jpg')."');";
//FOTO WARMART
    $logo_warmart = "".asset('/assets/img/examples/warmart_logo.png')."";
//PAGINATION DAFTAR PRODUK
    $produk_pagination = $data_produk->links();
//MENAMPILKAN KATEGORI
    $kategori_produk = $this->produkKategori($kategori);
    $data_kategori = $kategori->first();
    $nama_kategori = 'Hasil Pencarian : "'.$request->search.'"';

//TAMPILAN VIA HP
    $agent = new Agent();

    if ($data_produk->count() > 0) {

      $daftar_produk = "";
      foreach ($data_produk as $produks) {
        $warung = Warung::select(['name'])->where('id', $produks->id_warung)->first();

        $daftar_produk .= '      
        <div class="col-md-3 col-sm-6 col-xs-6">
          <div class="card cards card-pricing">
            <a href="'.url("/keranjang-belanja") .'">
              <div class="card-image">';
                if ($produks->foto != NULL) {
                  $daftar_produk .= '<img src="'.asset('foto_produk/'.$produks->foto.'').'">';
                }
                else{
                  $daftar_produk .= '<img src="'.asset('image/foto_default.png').'">';
                }
                $daftar_produk .= '
              </div>
            </a>
            <div class="card-content">
              <div class="footer">     
                <a href="'.url("/keranjang-belanja") .'" class="card-title">';
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
                    $daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" id="btnBeliSekarang" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja"><b style="font-size:18px"> Beli </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                  }
                  else{
                    $daftar_produk .= '<a href="'. url('/keranjang-belanja/tambah-produk-keranjang-belanja/'.$produks->id.''). '" id="btnBeliSekarang" class="btn btn-danger btn-round" rel="tooltip" title="Tambah Ke Keranjang Belanja"><b style="font-size:18px"> Beli Sekarang </b><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
                  }
                  $daftar_produk .= '
                </div>
              </div>
            </div>
          </div>';
        }
      }
      else{
        $daftar_produk = 
        '<div class="col-md-3">
        <div class="card card-product card-plain no-shadow" data-colored-shadow="false">
          <div class="card-image">
            <img src="'.asset('image/foto_default.png').'">
          </div>
          <div class="card-content">
            <a href="#">
              <h4 >Tidak Ada Produk</h4>
            </a>
          </div>
        </div>
      </div>';
    }        

    return view('layouts.daftar_produk', ['kategori_produk' => $kategori_produk, 'daftar_produk' => $daftar_produk, 'produk_pagination' => $produk_pagination, 'foto_latar_belakang' => $foto_latar_belakang, 'nama_kategori' => $nama_kategori, 'agent' => $agent,'cek_belanjaan'=>$cek_belanjaan,'logo_warmart'=>$logo_warmart]);
  }

}