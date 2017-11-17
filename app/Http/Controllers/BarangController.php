<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Html\Builder;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Barang;
use App\Hpp;
use Laratrust;
use File;
use Auth;
use Intervention\Image\ImageManagerStatic as Image;


class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
      $this->middleware('user-must-warung');
    }


    public function index(Request $request, Builder $htmlBuilder)
    {

      if (Auth::user()->id_warung == '') {
        Auth::logout();
        return response()->view('error.403');
      }else{

           // datatable
        if ($request->ajax()) {
                  # code...
          $data_barang = Barang::with(['satuan','kategori_barang'])->where('id_warung',Auth::user()->id_warung)->get();
          return Datatables::of($data_barang)
          ->addColumn('action', function($barang){ 
            return view('datatable._action_barang',[

              'model'             => $barang,
              'form_url'          => route('barang.destroy',$barang->id),
              'edit_url'          => route('barang.edit',$barang->id),
              'detail_url'        => route('barang.detail_produk',$barang->id),
              'confirm_message'   => 'Anda Yakin Mau Menghapus ' .$barang->nama_barang .' ?' 

            ]);
          })
          ->addColumn('nama_barang', function($barang){

            return title_case($barang->nama_barang);

          })
          ->addColumn('harga_beli', function($barang){

            $harga_beli = number_format($barang->harga_beli,0,',','.');

            return $harga_beli;

          })
          ->addColumn('harga_jual', function($barang){

            $harga_jual = number_format($barang->harga_jual,0,',','.');

            return $harga_jual;

          })
          ->addColumn('status_aktif', function($status){

            if ($status->status_aktif == 1) {
              return "Aktif";
            }else{
              return "Tidak Aktif";
            }

          })->make(true);
        }
        $html = $htmlBuilder
        ->addColumn(['data' => 'kode_barcode', 'name' => 'kode_barcode', 'title' => 'Barcode'])
        ->addColumn(['data' => 'kode_barang', 'name' => 'kode_barang', 'title' => 'Kode'])
        ->addColumn(['data' => 'nama_barang', 'name' => 'nama_barang', 'title' => 'Nama'])              
        ->addColumn(['data' => 'satuan.nama_satuan', 'name' => 'satuan.nama_satuan', 'title' => 'Satuan']) 
        ->addColumn(['data' => 'harga_beli', 'name' => 'harga_beli', 'title' => 'Harga Beli'])
        ->addColumn(['data' => 'harga_jual', 'name' => 'harga_jual', 'title' => 'Harga Jual']) 
        ->addColumn(['data' => 'status_aktif', 'name' => 'status_aktif', 'title' => 'Status']) 
        ->addColumn(['data' => 'kategori_barang.nama_kategori_barang', 'name' => 'kategori_barang.nama_kategori_barang', 'title' => 'Kategori'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Detail | Ubah | Hapus', 'orderable' => false, 'searchable'=>false]);

        return view('barang.index')->with(compact('html'));

      }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //        
      if (Auth::user()->id_warung == '') {
        Auth::logout();
        return response()->view('error.403');
      }else{

        return view('barang.create');
      }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      if (Auth::user()->id_warung == '') {
        Auth::logout();
        return response()->view('error.403');
      }else{
                      //validate
        $this->validate($request, [
          'kode_barcode'        => 'nullable|unique:barangs,kode_barcode,NULL,id,id_warung,'.Auth::user()->id_warung.'|max:191', 
          'kode_barang'         => 'required|unique:barangs,kode_barang,NULL,id,id_warung,'.Auth::user()->id_warung.'|max:191',
          'nama_barang'         => 'required|max:191',
          'harga_beli'          => 'required|numeric|digits_between:1,11',
          'harga_jual'          => 'required|numeric|digits_between:1,11',
          'kategori_barang_id'  => 'required|exists:kategori_barangs,id',
          'satuan_id'           => 'required|exists:satuans,id',
          'foto'                => 'image|max:3072'
        ]);

        if ($request->status_aktif == '') {

          $status_aktif = 0;

        }else{
         $status_aktif = $request->status_aktif;
       }

       if ($request->hitung_stok == '') {

        $hitung_stok = 0;

      }else{
        $hitung_stok  = $request->hitung_stok;
      }


      $insert_barang = Barang::create([
        'kode_barang'       => $request->kode_barang ,
        'kode_barcode'      => $request->kode_barcode, 
        'nama_barang'       => strtolower($request->nama_barang), 
        'harga_beli'        => $request->harga_beli, 
        'harga_jual'        => $request->harga_jual, 
        'satuan_id'         => $request->satuan_id, 
        'kategori_barang_id'=> $request->kategori_barang_id, 
        'status_aktif'      => $status_aktif, 
        'hitung_stok'       => $hitung_stok, 
        'konfirmasi_admin'  => 1,
        'id_warung'         => Auth::user()->id_warung]);

      if ($request->hasFile('foto')) {

        $foto = $request->file('foto');


        if (is_array($foto) || is_object($foto))
        {
          // Mengambil file yang diupload
          $uploaded_foto = $foto;
          // mengambil extension file
          $extension = $uploaded_foto->getClientOriginalExtension();
          // membuat nama file random berikut extension
          $filename = str_random(40) . '.' . $extension;

          $image_resize = Image::make($foto->getRealPath());              
          $image_resize->fit(300);
          $image_resize->save(public_path('foto_produk/' .$filename));
          $insert_barang->foto = $filename; 
          // menyimpan field foto_kamar di database kamar dengan filename yang baru dibuat
          $insert_barang->save();
        }

      }

      $pesan_alert = 
      '<div class="container-fluid">
      <div class="alert-icon">
      <b><i class="material-icons">check</i></b>
      </div>
      <b>BERHASIL:</b> Menambahkan Produk <b>'.$request->nama_barang.'</b>
      </div>';

      Session::flash("flash_notification", [
        "level"=>"success",
        "message"=>$pesan_alert
      ]);

      return redirect()->route('barang.index');

    }

  }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $barang = Barang::find($id);
      if ($barang->id_warung != Auth::user()->id_warung) {
        Auth::logout();
        return response()->view('error.403');
      }else{
        return view('barang.edit')->with(compact('barang'));
      }   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $update_barang = Barang::find($id);
      if ($update_barang->id_warung != Auth::user()->id_warung) {
        Auth::logout();
        return response()->view('error.403');
      }else{

            //validate
        $this->validate($request, [
          'kode_barcode'        => 'nullable|max:191|unique:barangs,kode_barcode,'. $id.',id,id_warung,'.Auth::user()->id_warung,
          'kode_barang'         => 'required|max:191|unique:barangs,kode_barang,'. $id.',id,id_warung,'.Auth::user()->id_warung,
          'nama_barang'         => 'required|max:191',
          'harga_beli'          => 'required|numeric|digits_between:1,11',
          'harga_jual'          => 'required|numeric|digits_between:1,11',
          'kategori_barang_id'  => 'required|exists:kategori_barangs,id',
          'satuan_id'           => 'required|exists:satuans,id',
          'foto'                => 'image|max:2048'

        ]);

        if ($request->status_aktif == '') {

          $status_aktif = 0;

        }else{
         $status_aktif = $request->status_aktif;
       }

       if ($request->hitung_stok == '') {

        $hitung_stok = 0;

      }else{
        $hitung_stok  = $request->hitung_stok;
      }


      $update_barang->update([
        'kode_barang'       => $request->kode_barang ,
        'kode_barcode'      => $request->kode_barcode, 
        'nama_barang'       => strtolower($request->nama_barang), 
        'harga_beli'        => $request->harga_beli, 
        'harga_jual'        => $request->harga_jual, 
        'satuan_id'         => $request->satuan_id, 
        'kategori_barang_id'=> $request->kategori_barang_id, 
        'status_aktif'      => $status_aktif, 
        'hitung_stok'       => $hitung_stok, 
        'konfirmasi_admin'  => 1,
        'id_warung'         => Auth::user()->id_warung
      ]);

      if ($request->hasFile('foto')) {

                  // Mengambil file yang diupload
        $foto = $request->file('foto');
        $uploaded_foto = $foto;
          // mengambil extension file
        $extension = $uploaded_foto->getClientOriginalExtension();
          // membuat nama file random berikut extension
        $filename = str_random(40) . '.' . $extension;

        $image_resize = Image::make($foto->getRealPath());              
        $image_resize->fit(300);
        $image_resize->save(public_path('foto_produk/' .$filename));

                              // hapus foto_home lama, jika ada
        if ($update_barang->foto) {
          $old_foto = $update_barang->foto;
          $filepath = public_path() . DIRECTORY_SEPARATOR . 'foto_produk'
          . DIRECTORY_SEPARATOR . $update_barang->foto;
          try {
            File::delete($filepath);
          } catch (FileNotFoundException $e) {
                              // File sudah dihapus/tidak ada
          }

        }

        $update_barang->foto = $filename; 

        $update_barang->save();

      }

      $pesan_alert = 
      '<div class="container-fluid">
      <div class="alert-icon">
      <b><i class="material-icons">check</i></b>
      </div>
      <b>BERHASIL:</b> Mengubah Produk <b>'.$request->nama_barang.'</b>
      </div>';

      Session::flash("flash_notification", [
        "level"=>"success",
        "message"=>$pesan_alert
      ]);

      return redirect()->route('barang.index');

    }

  }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
                // hapus 
      $barang = Barang::find($id);

      if ($barang->id_warung != Auth::user()->id_warung) {
        Auth::logout();
        return response()->view('error.403');
      }else{

        $cek_barang = Hpp::where('id_produk',$id)->count();
        if ($cek_barang > 0) {

          $pesan_alert = 
          '<div class="container-fluid">
          <div class="alert-icon">
          <b><i class="material-icons">check</i></b>
          </div>
          <b>GAGAL: Produk '.$barang->nama_barang.' Tidak Bisa Dihapus Karena Sudah Terpakai.</b>
          </div>';

          Session::flash("flash_notification", [
            "level"     => "danger",
            "message"   => $pesan_alert
          ]);
          return redirect()->route('barang.index');

        }else{
                      // jika gagal hapus
          if (!Barang::destroy($id)) {
                          // redirect back
            return redirect()->back();
          }else{
            if ($barang->foto != '') {

              $old_foto = $barang->foto;
              $filepath = public_path() . DIRECTORY_SEPARATOR . 'foto_produk'
              . DIRECTORY_SEPARATOR . $barang->foto;
              try {
                File::delete($filepath);
              } catch (FileNotFoundException $e) {
                                                          // File sudah dihapus/tidak ada
              }            
            }

            $pesan_alert = 
            '<div class="container-fluid">
            <div class="alert-icon">
            <b><i class="material-icons">check</i></b>
            </div>
            <b>BERHASIL:</b> Menghapus Produk <b>'.$barang->nama_barang.'</b>
            </div>';

            Session::flash("flash_notification", [
              "level"     => "danger",
              "message"   => $pesan_alert
            ]);
            return redirect()->route('barang.index');
          }
        }

      }
    }

    //HALAMAN DETAIL PRODUK
    public function detail_produk($id) {
      $barang = Barang::find($id);
      if ($barang->id_warung != Auth::user()->id_warung) {
        Auth::logout();
        return response()->view('error.403');
      }
      else{
        return view('barang.detail_produk', ['id' => $id, 'deskripsi_produk' => $barang->deskripsi_produk])->with(compact('barang'));
      }
    }

    //UPDATE DESKRIPSI PRODUK
    public function update_deskripsi_produk(Request $request) {
      $update_deskripsi_produk = Barang::find($request->id);
      if ($update_deskripsi_produk->id_warung != Auth::user()->id_warung) {
        Auth::logout();
        return response()->view('error.403');
      }
      else{
        $update_deskripsi_produk->update([
          'deskripsi_produk' => $request->deskripsi_produk
        ]);

        $pesan_alert = 
        '<div class="container-fluid">
        <div class="alert-icon">
        <b><i class="material-icons">check</i></b>
        </div>
        <b>BERHASIL:</b> Menambahkan Deskripsi Produk <b>'.$update_deskripsi_produk->nama_barang.'</b>
        </div>';

        Session::flash("flash_notification", [
          "level"=>"success",
          "message"=>$pesan_alert
        ]);

        return redirect()->route('barang.index');
      }
    }

    //LIHAT DESKRIPSI PRODUK
    public function lihat_deskripsi_produk($id) {
      $lihat_deskripsi_produk = Barang::find($id);
      $nama_produk = $lihat_deskripsi_produk->nama;

      if ($lihat_deskripsi_produk->id_warung != Auth::user()->id_warung) {
        Auth::logout();
        return response()->view('error.403');
      }
      else{
        return view('barang.lihat_deskripsi_produk', ['id' => $id, 'lihat_deskripsi_produk' => $lihat_deskripsi_produk, 'nama_produk' => $nama_produk]);
      }
    }
  }
