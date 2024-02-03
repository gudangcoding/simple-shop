<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;

class HomeController extends Controller
{
    public function index()
    {
        $barangs = Barang::paginate(20);
        return response()->json([
            'success' => true,
            'data' => $barangs,
            'status_code' => 201
        ], 201);
    }
}
