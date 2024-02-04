<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{

    public function profil(Request $request)
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $success =  $user;
        $success['token'] =  $user->createToken('MyApp', ['user'])->plainTextToken;
        //kirim email jika sudah sukses daftar atur di app\helpers\Kirimemail.php
        //jangan lupa tambahkan di composer.json
        //lalu cmd composer dump-autoload
        // "files": [
        //     "app/Helpers/KirimEmail.php"
        // ],
        // kirim_email($request->email, $request->name, "Pendaftaran Toko Online", "aktifkan pendaftaran anda disini<a href='https://tokokamu.com/user/aktifasi/".$user->id."'>Aktifasi Akun</a>", "");
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Register success!',
                'data' => $success,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Register Gagal!'
            ], 401);
        }
    }

    public function aktifasi(Request $request, $id)
    {
        $user = User::find($id);
        $user->aktif = 'Y';
        $user->update();
        return response()->json([
            'success' => true,
            'message' => 'Aktifasi success!',
            'data' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = User::select('id', 'name', 'email')->find(auth()->guard()->user()->id);
            $success =  $user;
            $success['token'] =  $user->createToken('MyApp', ['user'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login success!',
                'data' => $success,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Login Failed!',
            ], 401);
        }
    }


    public function logout(Request $request)
    {
        Auth::guard()->logout();
        return response()->json([
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ]);
    }

   function uploadfoto(Request $request)  {
    $user = User::find($request->id);
    $base64Image = $request->image;
    if ($base64Image) {
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
        $filename = 'foto_' . time() . '.jpg'; 
        Storage::put('public/foto/' . $filename, $image);
        $imageUrl = asset('storage/foto/' . $filename);
        $user->foto = $imageUrl;
    }
    $user->update();

    return response()->json([
        'success' => true,
        'data' => $user
    ], 201);
   } 
}
