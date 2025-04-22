@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Coupon</h3>
                </div>
                <form method="POST" class="m-5" action="{{ route('coupons.update', $coupon->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Coupon Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $coupon->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Coupon Code</label>
                        <input type="text" class="form-control" id="code" name="code" value="{{ $coupon->code }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description">{{ $coupon->description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="discount_type" class="form-label">Discount Type</label>
                        <select class="form-control" id="discount_type" name="discount_type">
                            <option value="percent" {{ $coupon->discount_type == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ $coupon->discount_type == 'fixed' ? 'selected' : '' }}>Fixed Amount (VND)</option>
                            <option value="free_shipping" {{ $coupon->discount_type == 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="discount_value" class="form-label">Discount Value</label>
                        <input type="number" class="form-control" id="discount_value" name="discount_value" value="{{ $coupon->discount_value }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="min_order_value" class="form-label">Minimum Order Value</label>
                        <input type="number" class="form-control" id="min_order_value" name="min_order_value" value="{{ $coupon->min_order_value }}">
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $coupon->start_date }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $coupon->end_date }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="usage_limit" class="form-label">Usage Limit</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="{{ $coupon->usage_limit }}">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" {{ $coupon->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $coupon->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="expired" {{ $coupon->status == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $coupon->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Enable Coupon</label>
                    </div>
                </div>
                    <button type="submit" class="btn btn-warning w-25">Update Coupon</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
