<?php

namespace App\Http\Controllers;

use App\DetailPesananPelanggan;
use App\KeranjangBelanja;
use App\PesananPelanggan;
use App\SettingAplikasi;
use Auth;
use Indonesia;
use Jenssegers\Agent\Agent;
use OpenGraph;
use SEOMeta;
use Illuminate\Http\Request;

class PesananPelangganController extends Controller
{
    public function pesananPelanggan()
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
        $agent = new Agent();

        $cek_belanjaan = KeranjangBelanja::where('id_pelanggan', Auth::user()->id)->count();
        
        //SETTING APLIKASI
        $setting_aplikasi = SettingAplikasi::select('tipe_aplikasi')->first();

        $pesanan_pelanggan  = PesananPelanggan::with('warung')->where('id_pelanggan', Auth::user()->id)->orderBy('id', 'desc')->paginate(10);
        $cek_pesanan        = $pesanan_pelanggan->count();
        $pagination_pesanan = $pesanan_pelanggan->links();
        //MEANMPILKAN PRODUK PESANAN VERSI KOMPUTER
        $produk_pesanan_komputer = '';
        $produk_pesanan_mobile = '';
        if ($agent->isMobile()) {
        //MEANMPILKAN PRODUK PESANAN VERSI MOBILE
            foreach ($pesanan_pelanggan as $pesanan_pelanggans) {

                $produk_pesanan_mobile .= '
                <div class="card">
                <div class="col-sm-6">
                <b>Pesanan : <a href="' . url('pesanan-detail?xasq=' . $pesanan_pelanggans->id . '') . '">#' . $pesanan_pelanggans->id . '</a></b>
                </div><hr style="margin-bottom: 0px;margin-top: 1px">
                <div class="col-sm-6">
                Waktu Pesan : ' . $pesanan_pelanggans->WaktuPesan . '
                </div>
                <div class="container">
                <a> Jumlah  : ' . $pesanan_pelanggans->jumlah_produk . '<a><br>
                Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp. ' . number_format($pesanan_pelanggans->subtotal, 0, ',', '.') . '<br>
                Status &nbsp;&nbsp;: ';

                if ($pesanan_pelanggans->konfirmasi_pesanan == 0) {
                    $produk_pesanan_mobile .= '<b  style="color:red">Belum Di Konfirmasi</b>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 1) {
                    $produk_pesanan_mobile .= '<b  style="color:orange">Sudah Diterima Warung</b>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 2) {
                    $produk_pesanan_mobile .= '<b  style="color:#01573e">Selesai</b>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 3) {
                    $produk_pesanan_mobile .= '<b  style="color:red">Batal</b>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 4) {
                    $produk_pesanan_mobile .= '<td><b  style="color:orange">Batal Pelanggan</b></td>';
                }
                if ($setting_aplikasi->tipe_aplikasi == 0) {
                    $produk_pesanan_mobile .= '<br>Warung : <a href="' . url('halaman-warung/' . $pesanan_pelanggans->id_warung . '') . '"><b>' . $pesanan_pelanggans->warung->name . '</b></a>';
                }

                $produk_pesanan_mobile .= '
                <a href="' . url('pesanan-detail?xasq=' . $pesanan_pelanggans->id . '') . '" style="background-color: #01573e" class="btn btn-block">Detail Pesanan</a>
                </div>
                </div>';
            }
        }else{
            foreach ($pesanan_pelanggan as $pesanan_pelanggans) {

                $produk_pesanan_komputer .= '
                <tr  style="margin-top:0px;margin-bottom: 0px;">
                <td><a href="' . url('pesanan-detail?xasq=' . $pesanan_pelanggans->id . '') . '"><b>#' . $pesanan_pelanggans->id . '</b></a></td>
                <td><b>' . $pesanan_pelanggans->WaktuPesan . '</b></td>
                <td class="text-right"><b class="text-right">Rp. ' . number_format($pesanan_pelanggans->subtotal, 0, ',', '.') . '</b></td>';
                if ($pesanan_pelanggans->konfirmasi_pesanan == 0) {
                    $produk_pesanan_komputer .= '<td class="text-center"><b  style="color:red">Belum Di Konfirmasi</b></td>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 1) {
                    $produk_pesanan_komputer .= '<td class="text-center"><b  style="color:orange">Sudah Diterima Warung</b></td>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 2) {
                    $produk_pesanan_komputer .= '<td class="text-center"><b  style="color:#01573e">Selesai</b></td>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 3) {
                    $produk_pesanan_komputer .= '<td class="text-center"><b  style="color:red">Batal</b></td>';
                } elseif ($pesanan_pelanggans->konfirmasi_pesanan == 4) {
                    $produk_pesanan_komputer .= '<td class="text-center"><b  style="color:orange">Batal Pelanggan</b></td>';
                }
                if ($setting_aplikasi->tipe_aplikasi == 0) {
                    $produk_pesanan_komputer .= '<td><a href="' . url('halaman-warung/' . $pesanan_pelanggans->id_warung . '') . '"><b>' . $pesanan_pelanggans->warung->name . '</b></a></td>';
                }
                $produk_pesanan_komputer .= '</tr>';
            }
        }



        return view('layouts.pesanan_pelanggan', ['produk_pesanan_mobile' => $produk_pesanan_mobile, 'produk_pesanan_komputer' => $produk_pesanan_komputer, 'cek_belanjaan' => $cek_belanjaan,'cek_pesanan' => $cek_pesanan, 'pagination_pesanan' => $pagination_pesanan, 'setting_aplikasi' => $setting_aplikasi]);
    }

    public function detailPesananPelanggan(Request $request)
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
        $agent = new Agent();

        $cek_belanjaan = KeranjangBelanja::where('id_pelanggan', Auth::user()->id)->count();

        $pesanan_pelanggan        = PesananPelanggan::with('warung')->where('id_pelanggan', Auth::user()->id)->where('id', $request->xasq)->first();
        $detail_pesanan_pelanggan = DetailPesananPelanggan::with(['produk', 'pelanggan', 'pesanan_pelanggan'])->where('id_pelanggan', Auth::user()->id)->where('id_pesanan_pelanggan', $pesanan_pelanggan->id)->paginate(10);
        //PERINTAH PAGINATION
        $pagination = $detail_pesanan_pelanggan->links();

        $status_pesanan = '';
        if ($pesanan_pelanggan->konfirmasi_pesanan == 0) {
            $status_pesanan .= '<td><b  style="color:red">Belum Di Konfirmasi</b></td>';
        } elseif ($pesanan_pelanggan->konfirmasi_pesanan == 1) {
            $status_pesanan .= '<td><b  style="color:orange">Sudah Diterima Warung</b></td>';
        } elseif ($pesanan_pelanggan->konfirmasi_pesanan == 2) {
            $status_pesanan .= '<td><b  style="color:#01573e">Selesai</b></td>';
        } elseif ($pesanan_pelanggan->konfirmasi_pesanan == 3) {
            $status_pesanan .= '<td><b  style="color:red">Batal</b></td>';
        } elseif ($pesanan_pelanggan->konfirmasi_pesanan == 4) {
            $status_pesanan .= '<td><b  style="color:orange">Batal Pelanggan</b></td>';
        }
        if ($pesanan_pelanggan->metode_pembayaran == 'TRANSFER') {
            $layanan_kurir = explode(" | ", $pesanan_pelanggan->layanan_kurir);
            $service = $layanan_kurir[2];
            $waktu_pengiriman = $layanan_kurir[1] ." Hari";
        }else{
            $service = "Bayar di Tempat";
            $waktu_pengiriman = "-";
        }
        $lokasi_warung = Indonesia::allVillages()->where('id', $pesanan_pelanggan->warung->wilayah)->first();

        return view('layouts.detail_pesanan_pelanggan', ['detail_pesanan_pelanggan' => $detail_pesanan_pelanggan, 'pesanan_pelanggan' => $pesanan_pelanggan, 'cek_belanjaan' => $cek_belanjaan, 'agent' => $agent,'status_pesanan' => $status_pesanan, 'lokasi_warung' => $lokasi_warung->name, 'pagination' => $pagination, 'service'=>$service, 'waktu_pengiriman'=>$waktu_pengiriman]);
    }

    public function batalPesananPelanggan($id)
    {
        PesananPelanggan::where('id', $id)->update(['konfirmasi_pesanan' => '4']);
        return redirect()->back();
    }

    public function lanjutPesananPelanggan($id)
    {
        PesananPelanggan::where('id', $id)->update(['konfirmasi_pesanan' => '0']);
        return redirect()->back();
    }

}
