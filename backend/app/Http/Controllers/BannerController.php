<?php

namespace App\Http\Controllers;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // thêm nếu dùng Storage
use Illuminate\Support\Str;
class BannerController extends Controller
{
    public function index(){
       $banners = Banner::all();
        return view('admin.banners.banner_list', compact('banners')); 
    }

    public function store( Request $request){
        return view('admin.banners.banner_create');
    }
    public function create( Request $request){
        $request->validate([
            'title' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'status' => 'required'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
        }

             Banner::create([
            'title' => $request->title,
            'image' => $imagePath,
            'status' => $request->status
        ]);
        return redirect('/admin/banners')->with('success', 'Banner created successfully.');
    }

    public function delete($id){
        $banner = Banner::find($id);
        $banner->delete();
        return redirect()->route('banners.index')->with('success', 'Banner deleted successfully.');
    }

    public function edit($id){
        $banner = Banner::find($id);
        return view('admin.banners.banner_edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'status' => 'required'
        ]);

            if($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('banners', 'public');
            }
        $banner = Banner::findOrFail($id);
        if(empty($imagePath)) {
            $imagePath = $banner->image;
        }
        $banner->update([
            'title' => $request->title,
            'image' => $imagePath,
            'status' => $request->status
        ]);
    
        return redirect()->route('banners.index')->with('success', 'Banner đã được cập nhật!');
    }
}