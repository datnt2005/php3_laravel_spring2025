@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Brands</h3>
                </div>
                <a href="/admin/brands/create" class="btn btn-primary mb-3 w-25 mt-3 ms-3">Create Brand</a>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Brand</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td>{{ $brand->id }}</td>
                            <td>{{ $brand->nameBrand }}</td>
                            <td>{{ $brand->description }}</td>
                            <td class="d-flex me-2">
                                <a href="/admin/brands/{{ $brand->id }}" class="btn btn-info btn-sm me-2">View</a>
                                <a href="/admin/brands/{{ $brand->id }}/edit" class="btn btn-warning btn-sm me-2">Edit</a>
                                <form action="{{ route('brands.delete',$brand->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                                <td colspan="4" class="text-center">No brands found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection