<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
    	$pesanans = Pesanan::where('user_id', Auth::user()->id)->where('status', '!=',0)->get();

    	return response()->json([
            'success'=>true,
            'data'=>$pesanans
        ],201);
    }

    public function detail($id)
    {
    	$pesanan = Pesanan::where('id', $id)->first();
    	$pesanan_details = PesananDetail::where('pesanan_id', $pesanan->id)->get();

        return response()->json([
            'success'=>true,
            'data'=>$pesanan_details
        ],201);
    }
}
