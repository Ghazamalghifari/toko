<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Http\Controllers\DaftarProdukController;
use App\KeranjangBelanja;
use App\SettingAplikasi;
use App\User;
use Auth;
use OpenGraph;
use SEOMeta;

class DetailProdukController extends Controller
{
    //
    public function detail_produk($id)
    {

        $this->seoDetailProduk();
        $barang               = Barang::find($id);
        $array_warung         = DaftarProdukController::dataWarungTervalidasi();
        $daftar_produk_sama   = $this->produkSekategori($barang, $array_warung);
        $daftar_produk_warung = $this->produkSewarung($barang, $array_warung);
        if (Auth::check()) {
            $cek_belanjaan = KeranjangBelanja::jumlahBelanja();
        } else {
            $cek_belanjaan = 0;
        }
        $setting_aplikasi = SettingAplikasi::select('tipe_aplikasi')->first();

        $sisa_stok_keluar = DaftarProdukController::cekStokProduk($barang);
        return view('layouts.detail_produk', ['id' => $id, 'barang' => $barang, 'cek_belanjaan' => $cek_belanjaan, 'daftar_produk_sama' => $daftar_produk_sama, 'daftar_produk_warung' => $daftar_produk_warung, 'cek_produk' => $sisa_stok_keluar,'setting_aplikasi'=>$setting_aplikasi]);

    }

    public function dataWarungTervalidasi()
    {
        $data_warung  = User::select(['id_warung'])->where('id_warung', '!=', 'NULL')->where('konfirmasi_admin', 1)->groupBy('id_warung')->get();
        $array_warung = array();
        foreach ($data_warung as $data_warungs) {
            array_push($array_warung, $data_warungs->id_warung);
        }

        return $array_warung;

    }

    public function produkSewarung($barang, $array_warung)
    {
        $data_produk          = Barang::where('foto', '!=', 'NULL')->where('id_warung', $barang->id_warung)->whereIn('id_warung', $array_warung)->inRandomOrder()->paginate(4);
        $daftar_produk_warung = DaftarProdukController::daftarProduk($data_produk);
        return $daftar_produk_warung;
    }

    public function produkSekategori($barang, $array_warung)
    {
        $data_produk        = Barang::where('foto', '!=', 'NULL')->where('kategori_barang_id', $barang->kategori_barang_id)->whereIn('id_warung', $array_warung)->inRandomOrder()->paginate(4);
        $daftar_produk_sama = DaftarProdukController::daftarProduk($data_produk);
        return $daftar_produk_sama;
    }
    public function seoDetailProduk()
    {
        SEOMeta::setTitle('thasyabusana.com');
        SEOMeta::setDescription('Thasya busana adalah usaha yang bergerak di bidang fashion. 
                                Yang mana kami lebih fokus dengan nuansa Tapis, 
                                seperti contoh (baju tapis, baju tenun,jilbab tapis,sarung tapis,peci tapis,kemeja tapis,sepatu/sendal tapis, dompet tapis,tas tapis, topi tapis dan juga souvenir/oleh oleh kerajinan khas lampung). 
                                kami juga menerima pesanan, baik satuan maupun dengan jumlah yang banyak.');
        SEOMeta::setCanonical('https://thasyabusana.com');
        SEOMeta::addKeyword(['baju tapis', 'baju tenun', 'jilbab tapis','sarung tapis', 'peci tapis', 'kemeja tapis', 'sepatu tapis','sendal tapis','dompet tapis','tas tapis','topis tapis','souvenir lampung','souvenir khas lampung','kerajinan lampung','oleh oleh lampung','thasya busana','lampung','bandar lampung']);

        OpenGraph::setDescription('Tasya Busana Adalah');
        OpenGraph::setTitle('thasyabusana.com');
        OpenGraph::setUrl('https://thasyabusana.com');
        OpenGraph::addProperty('type', 'articles');

    }

}
