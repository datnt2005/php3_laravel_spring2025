@extends('layouts.appAdmin')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Products</h3>
                </div>

                <div class="choose d-flex justify-content-end">
                    <form action="{{ route('products.index') }}" method="GET" class="w-75">
                        <input type="search" name="search" value="{{ request('search') }}" class="input-search mb-3 rounded  mt-3 ms-3 h-50 p-2" placeholder="Tìm kiếm sản phẩm...">
                        <button type="submit" class="btn btn-dark bg-gradient text-white">Tìm kiếm</button>
                    </form>
                    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3 w-25 mt-3 mx-3">Create Product</a>
                    <a href="{{ route('products.storeFile') }}" class="btn btn-secondary mb-3  mt-3 mx-3" style="width: 180px">Upload File</a>
                    
                </div>

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->slug }}</td>

                            <td>
                                {{ $product->productVariants->min('price') ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $product->productVariants->sum('quantityProduct') ?? 0 }}
                            </td>
                            <td>
                                @foreach($product->categories as $category)
                                    {{ $category->nameCategory }}, 
                                @endforeach
                            </td>
                            <td>{{ $product->brand->nameBrand ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                <form action="{{ route('products.delete', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this product?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No products found.</td>
                        </tr>
                        @endforelse
                        <div class="paginate mb-3 d-flex justify-content-end mx-3">
                            {{ $products->links('pagination::bootstrap-4') }}
                        </div>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

@endsection