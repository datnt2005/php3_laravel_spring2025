@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Banner</h3>
                </div>
                <form method="POST" class="m-5" action="{{ route('banners.update', $banner['id']) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="{{ $banner->title }}" required>

                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image"
                            value="{{ $banner->image }}" >
                        <img src="{{ asset('storage/' . $banner->image) }}" alt="banner" width="300" height="100" class="mt-2">
                    </div>
                    <div class="mb-3">
                        <label for="parent" class="form-label">Image</label>
                        <select name="status" id="status" class="form-control" value="{{ $banner->status }}">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection