@extends('layouts.appUser')
@section('content')
<main>
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mt-3">
                <li class="breadcrumb-item"><a href="index.html" class="nav-link">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Yêu thích</li>
            </ol>
        </nav>
    </div>
    <div class="banner-suit container mt-3">
        <a href="#">
            <img src="../../assets/images/specialDeal.jpg" alt class="w-100 h-75">
        </a>
    </div>
    <section>
        <div class="container mt-3">
            <div class="row">
                <div class="title-items">
                    <h4 class="text-center fw-bold mb-4">SẢN PHẨM YÊU THÍCH</h4>
                </div>
                @forelse ($favorites as $favorite)
                @php
                $product = $favorite->product;
                @endphp

                @if ($product)
                <div class="col-md-3">
                    <div class="products-item">
                        <div class="image-product">
                            <a href="{{ url('/product/' . $product->slug) }}" class="image-product-links">
                                @if ($product->productPic->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->productPic->first()->imagePath) }}"
                                    alt="sanpham">
                                @elseif ($product->productVariants->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->productVariants->first()->image) }}"
                                    alt="sanpham">
                                @else
                                <img src="{{ asset('images/no-image.png') }}" alt="Không có ảnh">
                                @endif
                            </a>
                            <a href="{{ url('/product/' . $product->slug) }}">
                                <button type="button"
                                    class="product-button border-0 add_cart_btn text-center text-decoration-none py-2 btn_add--checkout px-2"
                                    data-product-id="{{ $product->id }}">
                                    MUA NGAY
                                </button>
                            </a>
                        </div>

                        <div class="favorite mt-2 text-end">
                            <form action="{{ route('favorites.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-transparent border-0 p-0 text-dark"
                                    title="Bỏ yêu thích">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                        fill="currentColor" stroke="currentColor" stroke-width="1"
                                        stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart">
                                        <path
                                            d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <div class="info_product">
                            <a href="{{ url('/product/' . $product->slug) }}"
                                class="text-secondary text-uppercase fw-bold text-decoration-none">
                                {{ $product->name }}
                            </a>
                        </div>

                        <div class="price-product fw-bold">
                            @if(!empty($product->productVariants[0]->sale_price) &&
                            $product->productVariants[0]->sale_price > 0)
                            <span class="fw-medium fs-7 text-decoration-line-through text-muted">
                                {{ number_format($product->productVariants[0]->price) }}đ
                            </span>
                            <span class="sale-price ms-1 fw-bold">
                                {{ number_format($product->productVariants[0]->sale_price) }}đ
                            </span>
                            @else
                            <span class="sale-price">
                                {{ number_format($product->productVariants[0]->price) }}đ
                            </span>
                            @endif
                            <span class="fw-medium fs-7 text-decoration-underline">đ</span>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <p class="text-center">Không có sản phẩm yêu thích nào.</p>
                @endforelse

            </div>
        </div>
    </section>
</main>
@endsection