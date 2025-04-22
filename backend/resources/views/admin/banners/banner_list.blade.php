@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Banners</h3>
                </div>
                <a href="/admin/banners/create" class="btn btn-primary mb-3 w-25 mt-3 ms-3">Create banner</a>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td>{{ $banner->id }}</td>
                            <td>{{ $banner->title }}</td>
                            <td>
                                <img src="{{ asset('storage/' . $banner->image) }}" alt="Banner Image" width="300" height="100">    
                            </td>
                            <td>
                                @if($banner->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="d-flex me-2">
                                <a href="/banners/{{ $banner->id }}" class="btn btn-info btn-sm me-2">View</a>
                                <a href="/admin/banners/{{ $banner->id }}/edit" class="btn btn-warning btn-sm me-2">Edit</a>
                                <form action="{{ route('banners.delete',$banner->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                                <td colspan="5" class="text-center">No banners found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection