<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::all();
        return view('admin.categories.category_list', compact('categories')); 
    }

    public function store( Request $request){

        $categories = Category::all();
        return view('admin.categories.category_create', compact('categories'));
    }
    public function create( Request $request){
        $request->validate([
            'nameCategory' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|unique:categories,slug',
        ]);

        if($request->slug == null){
            $slug = Str::slug($request->nameCategory);
            $request->merge(['slug' => $slug]);
        }
        Category::create([
            'nameCategory' => $request->nameCategory,
            'parent_id' => $request->parent_id,
            'slug' => $request->slug,
        ]);
        return redirect('/admin/categories')->with('success', 'Category created successfully.');
    }

    public function delete($id){
        $category = Category::find($id);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function edit($id){
        $category = Category::findOrFail($id);
        $categories = Category::all();
        return view('admin.categories.category_edit', compact('category', 'categories'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nameCategory' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|unique:categories,slug,' . $id,
        ]);
        if($request->slug == null){
            $slug = Str::slug($request->nameCategory);
            $request->merge(['slug' => $slug]);
        }
        $category = Category::findOrFail($id);
        $category->update([
            'nameCategory' => $request->nameCategory,
            'parent_id' => $request->parent_id,
            'slug' => $request->slug,
        ]);
    
        return redirect()->route('categories.index')->with('success', 'Sản phẩm đã được cập nhật!');
    }
}
