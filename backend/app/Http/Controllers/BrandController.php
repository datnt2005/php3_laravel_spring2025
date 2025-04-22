<?php

namespace App\Http\Controllers;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(){
        $brands = Brand::all();
        return view('admin.brands.brand_list', compact('brands')); 
    }

    public function store( Request $request){
        return view('admin.brands.brand_create');
    }
    public function create( Request $request){
        $request->validate([
            'nameBrand' => 'required',
            'description' => 'required',
        ]);
        Brand::create([
            'nameBrand' => $request->nameBrand,
            'description' => $request->description
        ]);
        return redirect('/admin/brands')->with('success', 'Brand created successfully.');
    }

    public function delete($id){
        $brand = Brand::find($id);
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }

    public function edit($id){
        $brand = Brand::find($id);
        return view('admin.brands.brand_edit', compact('brand'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nameBrand' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    
        $brand = Brand::findOrFail($id);
        $brand->update([
            'nameBrand' => $request->nameBrand,
            'description' => $request->description
        ]);
    
        return redirect()->route('brands.index')->with('success', 'Thương hiệu đã được cập nhật!');
    }
}
