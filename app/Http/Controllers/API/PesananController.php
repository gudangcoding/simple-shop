<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\PesananDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

use Midtrans\Config;
use Midtrans\Snap;



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

    public function store(Request $request)
    {

        // Set konfigurasi Midtrans
        Config::$clientKey = config('midtrans.clientKey'); //untuk core api
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $tanggal = Carbon::now();
        // //create order
        $order = Pesanan::create([
            'user_id' => $request->user_id,
            'tanggal' => $tanggal,
            'jumlah_barang' => $request->jumlah_barang,
            'jumlah_harga' => $request->jumlah_harga,
            'kode' => mt_rand(100, 999),
            'status' => 0,
        ]);

        $params = array(
            'transaction_details' => array(
                'order_id' => $order->id,
                'gross_amount' => $request->jumlah_harga,
            )
        );

        // $snapToken = \Midtrans\Snap::getSnapToken($params);
        // return response()->json($snapToken);


        // //create order item
        foreach ($request->items as $item) {

            PesananDetail::create([
                'pesanan_id' => $order->id,
                'barang_id' => $item['id'],
                'jumlah' => $item['quantity'],
            ]);
        }

        try {
            // Get Snap Payment Page URL
            $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
            // return response()->json($paymentUrl);
            header('Location: ' . $paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $pesanan = Pesanan::find($order->id);
        $pesanan->url_bayar = $paymentUrl;
        $pesanan->update();

        // //response
        return response()->json([
            'success' => true,
            'url' => $paymentUrl,
            'message' => 'Order Created',
        ], 201);
    }

    public function handle(Request $request)
    {
        //ini akan di kirim dari midtrans
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $fraud = $notif->fraud_status;
        return response()->json($notif);


        error_log("Order ID $notif->order_id: " . "transaction status = $transaction, fraud staus = $fraud");

        if ($transaction == 'capture') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'challenge'
            } else if ($fraud == 'accept') {
                $pesanan = Pesanan::find($notif->order_id);
                $pesanan->status = 'success';
                $pesanan->update();
            }
        } else if ($transaction == 'cancel') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'failure'
            } else if ($fraud == 'accept') {
                $pesanan = Pesanan::find($notif->order_id);
                $pesanan->status = 'batal';
                $pesanan->update();
            }
        } else if ($transaction == 'deny') {
            $pesanan = Pesanan::find($notif->order_id);
                $pesanan->status = 'gagal';
                $pesanan->update();
        }
    }

    function status(Request $request)
    {
        $status = \Midtrans\Transaction::status($request->order_id);
        return response()->json([
            'success' => true,
            'data' => $status
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
