@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Category</h3>
                </div>
                <form method="POST" class="m-5" action="{{ route('categories.update', $category['id']) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Category</label>
                        <input type="text" class="form-control" id="name" name="nameCategory"
                            value="{{ $category->nameCategory }}" required>

                    </div>
                    <div class="mb-3">
                        <label for="parent" class="form-label">Category Parent</label>
                        <select name="parent_id" id="parent" class="form-control">
                            <option value="{{ $category['parent_id'] }}">{{ $category->parent->nameCategory ?? "" }}
                            </option>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nameCategory }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input class="form-control" id="slug" name="slug" value="{{ $category->slug }}">
                    </div>
                    <button type="submit" class="btn btn-warning">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection