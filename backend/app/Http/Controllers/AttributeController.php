<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return view('admin.attributes.attribute_list', compact('attributes'));
    }

    public function store()
    {
        return view('admin.attributes.attribute_create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'attributes.*.value' => 'nullable|string|max:255', // Mỗi giá trị trong mảng phải là chuỗi
        ]);
        
        if(Attribute::where('name', $request->name)->exists()){
            return redirect()->back()->with('error', 'Tên thuộc tính đã tồn tại!');
        }
        if(Attribute::where('slug', $request->slug)->exists()){
            return redirect()->back()->with('error', 'Slug thuộc tính đã tồn tại!');
        }
        if ($request->slug == null) {
            $slug = Str::slug($request->name);
            $request->merge(['slug' => $slug]);
        }
        // Tạo thuộc tính
        $attribute = Attribute::create(['name' => $request->name, 'slug' => $request->slug]);

        $attributes = $request->input('attributes', []);

        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attributeValue) {
                if (!empty($attributeValue['value'])) {
                    $attribute->values()->create(['value' => $attributeValue['value']]);
                }
            }
        } else {
            dd("Không có giá trị thuộc tính");
        }
    
        return redirect()->route('attributes.index')->with('success', 'Thêm thuộc tính thành công!');
    }

    public function edit($id)
    {
        $attribute = Attribute::with('values')->findOrFail($id);
        return view('admin.attributes.attribute_edit', compact('attribute'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'attributes.*.value' => 'nullable|string|max:255', // Mỗi giá trị trong mảng phải là chuỗi
        ]);
    
        if(Attribute::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return redirect()->back()->with('error', 'Tên thuộc tính đã tồn tại!');
        }
        if(Attribute::where('slug', $request->slug)->where('id', '!=', $id)->exists()) {
            return redirect()->back()->with('error', 'Slug thuộc tính đã tồn tại!');
        }

        $attribute = Attribute::findOrFail($id);
    
        // Nếu slug rỗng, tự động tạo từ name
        if (!$request->slug) {
            $slug = Str::slug($request->name);
        } else {
            $slug = $request->slug;
        }
    
        $attribute->update([
            'name' => $request->name,
            'slug' => $slug
        ]);
    
        // Xóa các giá trị cũ trước khi thêm mới
        $attribute->values()->delete();
        $attributes = $request->input('attributes', []);
        // Thêm các giá trị mới nếu có
        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attributeValue) {
                if (!empty($attributeValue['value'])) {
                    $attribute->values()->create(['value' => $attributeValue['value']]);
                }
            }
        } else {
            dd("Không có giá trị thuộc tính");
        }
    
        return redirect()->route('attributes.index')->with('success', 'Attribute updated successfully!');
    }

    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return redirect()->route('attributes.index')->with('success', 'Attribute deleted successfully!');
    }
}