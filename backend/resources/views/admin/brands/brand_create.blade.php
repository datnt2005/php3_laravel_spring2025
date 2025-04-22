@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Brand</h3>
                </div>
                <div class="card-body">
                    {{-- Form nhập liệu --}}
                    <form method="POST" action="{{ route('brands.create') }}" class="m-5">
                        @csrf  {{-- Bảo mật form --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Brand Name</label>
                            <input type="text" class="form-control" id="name" name="nameBrand" value="{{ old('nameBrand') }}">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
                        </div>

                        <button type="submit" class="btn btn-success">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
