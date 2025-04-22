<?php

namespace App\Http\Controllers;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(){
        $coupons = Coupon::all();
        return view('admin.coupons.coupon_list', compact('coupons')); 
    }

    public function store( Request $request){
        return view('admin.coupons.coupon_create');
    }
    public function create( Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,expired',
            'is_active' => 'required|boolean',
        ]);
        Coupon::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_value' => $request->min_order_value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'usage_limit' => $request->usage_limit,
            'used_count' => 0,
            'status' => $request->status,
            'is_active' => $request->is_active
        ]);
        return redirect('/admin/coupons')->with('success', 'Coupon created successfully.');
    }

    public function delete($id){
        $coupon = Coupon::find($id);
        if ($coupon) {
            $coupon->delete();
            return redirect('/admin/coupons')->with('success', 'Coupon deleted successfully.');
        } else {
            return redirect('/admin/coupons')->with('error', 'Coupon not found.');
        }
    }
    public function show($id){
        $coupon = Coupon::find($id);
        return view('admin.coupons.coupon_detail', compact('coupon'));
    }

    public function edit($id){
        $coupon = Coupon::find($id);
        return view('admin.coupons.coupon_edit', compact('coupon'));
    }

    public function update(Request $request, $id){
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,expired',
            'is_active' => 'required|boolean',
        ]);
        $coupon = Coupon::find($id);
        $coupon->update($request->all());
        return redirect('/admin/coupons')->with('success', 'Coupon updated successfully.');
    }
}
