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

    public function detail($id)
    {
        $barangs = Barang::find($id);
        
        return response()->json([
            'success'=>true,
            'data'=>$barangs
        ],201);
    }
    public function cari(Request $request)
    {
        $keyword = $request->q;
        // Lakukan pencarian di tabel 'barangs' berdasarkan nama_barang
        $barangs = Barang::where('nama_barang', 'like', "%$keyword%")->get();
        
        return response()->json([
            'success'=>true,
            'data'=>$barangs
        ],201);
    }
}
