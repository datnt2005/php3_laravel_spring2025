@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Banner</h3>
                </div>
                <div class="card-body">
                    {{-- Form nhập liệu --}}
                    <form method="POST" action="{{ route('banners.create') }}" enctype="multipart/form-data" class="m-5">
                        @csrf  {{-- Bảo mật form --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" value="{{ old('image') }}">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="parent" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
