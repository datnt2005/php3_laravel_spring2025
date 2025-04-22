@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Category</h3>
                </div>
                <div class="card-body">
                    {{-- Form nhập liệu --}}
                    <form method="POST" action="{{ route('categories.create') }}" class="m-5">
                        @csrf  {{-- Bảo mật form --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="nameCategory" value="{{ old('nameCategory') }}">
                        </div>
                        <div class="mb-3">
                            <label for="parent" class="form-label">Category Parent</label>
                            <select name="parent_id" id="parent" class="form-control">
                                <option value="">None</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->nameCategory }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input class="form-control" id="slug" name="slug" >{{ old('slug') }}
                        </div>

                        <button type="submit" class="btn btn-success">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
