<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        // Validasi gambar yang di-upload
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Menyimpan gambar dengan nama unik
        $imageName = time() . '.' . $request->image->extension();
        $path = $request->image->storeAs('public/images', $imageName);

        // Mengambil path lengkap untuk gambar
        $imagePath = asset('storage/images/' . $imageName);

        return view('upload', compact('imagePath'));
    }
}
