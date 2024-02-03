<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::paginate(10);
        
        return response()->json([
            'success'=>true,
            'data'=>$barangs
        ],201);
    }
}
