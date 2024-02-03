<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\PesananDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $orders = Pesanan::with('user')
            ->orderBy('created_at', 'desc')
            ->where('pesanans.user_id', $request->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ], 201);
    }
    public function history(Request $request)
    {
        $orders = Pesanan::with('user')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->where('pesanans.user_id', $request->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ], 201);
    }

    public function myorder(Request $request)
    {
        $orders = Pesanan::with('pesanan_detail')
            ->orderBy('created_at', 'desc')
            ->where('pesanans.id', $request->id);

        return response()->json([
            'success' => true,
            'data' => $orders
        ], 201);
    }

    public function pesan(Request $request, $id)
    {
        $barang = Barang::where('id', $id)->first();
        $tanggal = Carbon::now();

        //validasi apakah melebihi stok
        if ($request->jumlah_pesan > $barang->stok) {
            return redirect('pesan/' . $id);
        }

        //cek validasi
        $cek_pesanan = Pesanan::where('user_id', $request->id)->where('status', 0)->first();
        //simpan ke database pesanan
        if (empty($cek_pesanan)) {
            $pesanan = new Pesanan;
            $pesanan->user_id = $request->id;
            $pesanan->tanggal = $tanggal;
            $pesanan->status = 0;
            $pesanan->jumlah_harga = 0;
            $pesanan->kode = mt_rand(100, 999);
            $pesanan->save();
        }


        //simpan ke database pesanan detail
        $pesanan_baru = Pesanan::where('user_id', $request->id)->where('status', 0)->first();

        //cek pesanan detail
        $cek_pesanan_detail = PesananDetail::where('barang_id', $barang->id)->where('pesanan_id', $pesanan_baru->id)->first();
        if (empty($cek_pesanan_detail)) {
            $pesanan_detail = new PesananDetail;
            $pesanan_detail->barang_id = $barang->id;
            $pesanan_detail->pesanan_id = $pesanan_baru->id;
            $pesanan_detail->jumlah = $request->jumlah_pesan;
            $pesanan_detail->jumlah_harga = $barang->harga * $request->jumlah_pesan;
            $pesanan_detail->save();
        } else {
            $pesanan_detail = PesananDetail::where('barang_id', $barang->id)->where('pesanan_id', $pesanan_baru->id)->first();

            $pesanan_detail->jumlah = $pesanan_detail->jumlah + $request->jumlah_pesan;

            //harga sekarang
            $harga_pesanan_detail_baru = $barang->harga * $request->jumlah_pesan;
            $pesanan_detail->jumlah_harga = $pesanan_detail->jumlah_harga + $harga_pesanan_detail_baru;
            $pesanan_detail->update();
        }

        //jumlah total
        $pesanan = Pesanan::where('user_id', $request->id)->where('status', 0)->first();
        $pesanan->jumlah_harga = $pesanan->jumlah_harga + $barang->harga * $request->jumlah_pesan;
        $pesanan->update();

        return response()->json([
            'success' => true,
            'data' => $pesanan
        ], 201);
    }

    public function check_out(Request $request)
    {
        $pesanan = Pesanan::where('user_id', $request->id)->where('status', 0)->first();
        $pesanan_details = [];
        if (!empty($pesanan)) {
            $pesanan_details = PesananDetail::where('pesanan_id', $pesanan->id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $pesanan_details
        ], 201);
    }

    public function delete(Request $request)
    {
        $pesanan_detail = PesananDetail::where('id', $request->id)->first();

        $pesanan = Pesanan::where('id', $pesanan_detail->pesanan_id)->first();
        $pesanan->jumlah_harga = $pesanan->jumlah_harga - $pesanan_detail->jumlah_harga;
        $pesanan->update();


        $pesanan_detail->delete();
        return response()->json([
            'success' => true,
            'data' => $pesanan_detail
        ], 201);
    }

    public function konfirmasi(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        if (empty($user->alamat)) {
            return redirect('check-out')->with('Error', 'Identitasi Harap dilengkapi!');
            // Alert::error('Identitasi Harap dilengkapi', 'Error');
            // return redirect('profile');
        }

        if (empty($user->nohp)) {
            return redirect('profile')->with('Error', 'Identitasi Harap dilengkapi!');
        }

        $pesanan = Pesanan::where('user_id', $request->id)->where('status', 0)->first();
        $pesanan_id = $pesanan->id;
        $pesanan->status = 1;
        $pesanan->update();

        $pesanan_details = PesananDetail::where('pesanan_id', $pesanan_id)->get();
        foreach ($pesanan_details as $pesanan_detail) {
            $barang = Barang::where('id', $pesanan_detail->barang_id)->first();
            $barang->stok = $barang->stok - $pesanan_detail->jumlah;
            $barang->update();
        }


        $pesanan_detail->delete();
        return response()->json([
            'success' => true,
            'data' => $pesanan_details
        ], 201);
    }
}
