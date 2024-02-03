<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfilController extends Controller
{
    public function index(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        return response()->json([
            'success' => true,
            'data' => $user
        ], 201);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'password'  => 'confirmed',
        ]);

        $user = User::where('id', $request->id)->first();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->nohp = $request->nohp;
        $user->alamat = $request->alamat;

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update();

        return response()->json([
            'success' => true,
            'data' => $user
        ], 201);
    }
}
