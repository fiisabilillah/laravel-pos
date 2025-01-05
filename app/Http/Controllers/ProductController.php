<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        //get data products
        $products = DB::table('products')
            ->when($request->input('name'), function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        //sort by created_at desc

        return view('pages.products.index', compact('products'));
    }

    public function create()
    {
        $categories = DB::table('categories')->get();
        return view('pages.products.create', compact('categories'));
    }

    public function store(Request $request)
{
    // Validasi input
    $request->validate([
        'name' => 'required|min:3|unique:products',
        'price' => 'required|integer',
        'stock' => 'required|integer',
        'category_id' => 'required',
        'image' => 'required|image|mimes:png,jpg,jpeg'
    ]);

    // Menyimpan gambar dengan nama file yang unik
    $filename = time() . '.' . $request->image->extension();
    $path = $request->image->storeAs('public/products', $filename);

    // Mendapatkan path lengkap file
    $filePath = storage_path('app/' . $path);

    // Mengubah izin file agar bisa diakses oleh web server
    chmod($filePath, 0777); 

    // Mengambil data lainnya
    $data = $request->all();
    $category = DB::table('categories')->where('id', $request->category_id)->first();

    // Menyimpan produk baru ke database
    $product = new \App\Models\Product;
    $product->name = $request->name;
    $product->price = (int) $request->price;
    $product->stock = (int) $request->stock;
    $product->category = $category->name;
    $product->category_id = $request->category_id;
    $product->image = $filename;  // Menyimpan nama file gambar di database
    $product->save();

    // Mengarahkan pengguna kembali ke daftar produk dengan pesan sukses
    return redirect()->route('product.index')->with('success', 'Product successfully created');
}


    public function edit($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        $categories = DB::table('categories')->get();
        return view('pages.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $product = \App\Models\Product::findOrFail($id);
        $category = DB::table('categories')->where('id', $request->category_id)->first();
        $data['category'] = $category->name;
        $product->update($data);
        return redirect()->route('product.index')->with('success', 'Product successfully updated');
    }

    public function destroy($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Product successfully deleted');
    }
}
