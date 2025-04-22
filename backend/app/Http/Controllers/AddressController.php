<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses;
        return view('user.address.address_list', compact('addresses'));
    }

    public function getDetails($id)
    {
        try { 
            $address = Address::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'address' => [
                    'province_id' => $address->province_id,
                    'district_id' => $address->district_id,
                    'ward_code' => $address->ward_code
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy chi tiết địa chỉ: ' . $e->getMessage()
            ], 500);
        }
    }
    public function create(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|string|max:255',
            'district_id' => 'required|string|max:255',
            'ward_code' => 'required|string|max:255',
            'detail' => 'required|string|max:255',
            ]);
        $validated['user_id'] = $user->id;
       if ($request->is_default) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }
        $address = Address::create($validated);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Thêm địa chỉ thành công',
            'data' => $address
        ]);
    }

    public function destroy($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Địa chỉ đã được xóa']);
    }

    public function edit($id)
    {
        $address = Address::findOrFail($id);
        return response()->json($address);
    }
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:addresses,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,15',
            'province_id' => 'required|string',
            'district_id' => 'required|string',
            'ward_code' => 'required|string',
            'detail' => 'required|string',
            'is_default' => 'nullable|boolean',
        ]);
    
        $address = Address::findOrFail($request->id);
    
        // If the address is set as default, unset other default addresses
        if ($request->is_default) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }
    
        $address->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'province_id' => $request->province_id,
            'district_id' => $request->district_id,
            'ward_code' => $request->ward_code,
            'detail' => $request->detail,
            'is_default' => $request->is_default ?? false,
        ]);
    
        return response()->json(['status' => 'success', 'message' => 'Địa chỉ đã được cập nhật thành công!']);
    }


}
