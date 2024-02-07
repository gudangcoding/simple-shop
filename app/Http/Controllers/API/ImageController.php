<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ImageController extends Controller
{
    function uploadfoto(Request $request)
    {     
        $base64Image = $request->image;
        if ($base64Image) {
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            $filename = 'foto_' . time() . '.jpg';
            Storage::put('public/foto/' . $filename, $image);
            $imageUrl = asset('storage/foto/' . $filename);
        }

        return $imageUrl;
    }
}
