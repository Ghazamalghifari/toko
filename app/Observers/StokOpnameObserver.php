<?php

namespace App\Observers;

use App\Hpp;
use App\StokOpname;
use Illuminate\Support\Facades\DB;

class StokOpnameObserver
{
    //MEMBUAT HPP MASUK
    public function creating(StokOpname $StokOpname)
    {
        $selisih_fisik = $StokOpname->selisih_fisik;

        if ($selisih_fisik < 0) {

            $selisih_fisik = $selisih_fisik * -1;
            $total_nilai   = $StokOpname->total * -1;
            Hpp::create([
                'no_faktur'       => $StokOpname->no_faktur,
                'id_produk'       => $StokOpname->produk_id,
                'jenis_transaksi' => 'Stok Opname',
                'jumlah_keluar'   => $selisih_fisik,
                'harga_unit'      => $StokOpname->harga,
                'total_nilai'     => $total_nilai,
                'jenis_hpp'       => '2',
                'warung_id'       => $StokOpname->warung_id,
            ]);

        } else {

            Hpp::create([
                'no_faktur'       => $StokOpname->no_faktur,
                'id_produk'       => $StokOpname->produk_id,
                'jenis_transaksi' => 'Stok Opname',
                'jumlah_masuk'    => $selisih_fisik,
                'harga_unit'      => $StokOpname->harga,
                'total_nilai'     => $StokOpname->total,
                'jenis_hpp'       => '1',
                'warung_id'       => $StokOpname->warung_id,
            ]);

        }

        return true;
    } // OBERVERS CREATING

    //HAPUS ITEM MASUK
    public function deleting(StokOpname $StokOpname)
    {
        $stok = Hpp::select([DB::raw('IFNULL(SUM(jumlah_masuk),0) - IFNULL(SUM(jumlah_keluar),0) as stok_produk')])
            ->where('no_faktur', '!=', $StokOpname->no_faktur)
            ->where('id_produk', $StokOpname->produk_id)
            ->where('warung_id', $StokOpname->warung_id)->first()->stok_produk;

        if ($stok < 0) {
            return false;
        } else {
            if ($StokOpname->selisih_fisik < 0) {
                $jenis_hpp = 2;
            } else {
                $jenis_hpp = 1;
            }
            $hpp = Hpp::where('no_faktur', $StokOpname->no_faktur)
                ->where('id_produk', $StokOpname->produk_id)
                ->where('jenis_hpp', $jenis_hpp)
                ->where('warung_id', $StokOpname->warung_id);

            if (!$hpp->delete()) {
                return false;
            } else {
                return true;
            }
        }
    }
}
