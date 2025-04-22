@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Coupons</h3>
                </div>
                <a href="/admin/coupons/create" class="btn btn-primary mb-3 w-25 mt-3 ms-3">Create Coupon</a>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Discount</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Use</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>{{ $coupon->name }}</td>
                            <td>{{ $coupon->discount_value }}</td>
                            <td>{{ $coupon->discount_type }}</td>
                            <td>{{ $coupon->usage_limit }}</td>
                            <td>{{ $coupon->used_count }}</td>
                            <td>
                                @if($coupon->status == "active")
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="d-flex me-2">
                                <a href="/admin/coupons/{{ $coupon->id }}/edit" class="btn btn-warning btn-sm me-2">Edit</a>
                                <form action="{{ route('coupons.delete',$coupon->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                                <td colspan="8" class="text-center">No coupons found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection