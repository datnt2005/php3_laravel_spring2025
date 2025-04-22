@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attributes</h3>
                </div>
                <a href="/admin/attributes/create" class="btn btn-primary mb-3 w-25 mt-3 ms-3">Create attribute</a>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($attributes as $attribute)
                        <tr>
                            <td>{{ $attribute->id }}</td>
                            <td>{{ $attribute->name }}</td>
                            <td>{{ $attribute->slug }}</td>
                            <td>{{ $attribute->values->implode('value', ', ') }}</td>
                            <td class="d-flex me-2">
                                <a href="/admin/attributes/{{ $attribute->id }}" class="btn btn-info btn-sm me-2">View</a>
                                <a href="/admin/attributes/{{ $attribute->id }}/edit" class="btn btn-warning btn-sm me-2">Edit</a>
                                <form action="{{ route('attributes.delete',$attribute->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                                <td colspan="4" class="text-center">No attributes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection