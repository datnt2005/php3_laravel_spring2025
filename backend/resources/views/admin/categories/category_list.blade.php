@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Categories</h3>
                </div>
                <a href="/admin/categories/create" class="btn btn-primary mb-3 w-25 mt-3 ms-3">Create Category</a>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Category Parent</th>
                            <th>Slug</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->nameCategory }}</td>
                            <td>{{ $category->parent ? $category->parent->nameCategory : 'None' }}</td>
                            <td>{{ $category->slug }}</td>
                            <td class="d-flex me-2">
                                <a href="/categories/{{ $category->id }}" class="btn btn-info btn-sm me-2">View</a>
                                <a href="/admin/categories/{{ $category->id }}/edit" class="btn btn-warning btn-sm me-2">Edit</a>
                                <form action="{{ route('categories.delete',$category->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                                <td colspan="3" class="text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection