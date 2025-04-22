@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Product File</h3>
                </div>
                <form action="{{ route('products.createFile') }}" method="POST" class="m-5" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" class="form-control w-25" required>
                    <button type="submit" class="btn btn-success mt-3">Import Excel</button>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection