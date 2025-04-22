@extends('layouts.appUser')

@section('content')
<main>
    <div class="container mt-3">
        <div class="row mb-4">
            <div id="carouselExampleIndicators" class="carousel slide w-100 custom-carousel" data-bs-ride="carousel">

                <!-- <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="../../assets/images/banner1.jpg" class="d-block w-100" alt="Slide 1">
                    </div>
                    <div class="carousel-item">
                        <img src="../../assets/images/banner2.jpg" class="d-block w-100" alt="Slide 2">
                    </div>
                    <div class="carousel-item">
                        <img src="../../assets/images/banner3.jpg" class="d-block w-100" alt="Slide 3">
                    </div>
                </div> -->
                <div class="carousel-inner">
                @forelse($banners as $banner)
                <div class="carousel-item active">
                        <img src="{{ asset('storage/' . $banner->image) }}" class="d-block w-100" alt="{{ $banner->title }}">
                    </div>
                @empty
                    <p class="text-center">Không có dữ liệu</p>
                @endforelse
                </div>
                <button class="carousel-control-prev custom-prev" type="button"
                    data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next custom-next" type="button"
                    data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
    <section class="">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="start-item">
                        <img src="../../assets/images/somitretrung.jpg" alt>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="start-item">
                        <img src="../../assets/images/shortthoaimai.jpg" alt>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="start-item">
                        <img src="../../assets/images/tshirtnangdong.jpg" alt>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="container mt-4">
        <div class="row banner-2">
            <img src="https://owen.cdn.vccloud.vn/media/codazon/slideshow/a/r/artboard_5-100_210524.jpg" alt>
        </div>
    </div>

    <section>
        <div class="container mt-3">
            <div class="row">
                <div class="title-items">
                    <h4 class="text-center fw-bold mb-4">BÁN CHẠY NHẤT</h4>
                </div>
                @forelse ($products as $product)
                <div class="col-md-3 mb-4">
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
                                    data-product-id="1">
                                    MUA NGAY
                                </button>
                            </a>
                        </div>

                        <div class="favorite mt-2 text-end">
                            <form action="{{ route('favorites.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                class="bg-transparent border-0 p-0 {{ in_array($product->id, $favoriteIds) ? 'text-dark' : 'text-white border border-dark' }}"                                    title="{{ in_array($product->id, $favoriteIds) ? 'Bỏ yêu thích' : 'Thêm yêu thích' }}">
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
                @endforeach
            </div>
        </div>
    </section>

    <div class="banner-suit container mt-5">
        <a href="#">
            <img src="../../assets/images/specialDeal.jpg" alt class="w-100">
        </a>
    </div>
    <section>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="image-left-form">
                        <img src="https://owen.cdn.vccloud.vn/media/amasty/ampromobanners/Artboard_10-100_210524.jpg"
                            alt class="w-100">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-status px-4 p-5">
                        <h4 class="text-center fw-bold pt-4">ĐĂNG KÝ NHẬN BẢN TIN</h4>
                        <p class="text-center register-text">Đừng bỏ lỡ hàng ngàn sản phẩm và chương trình siêu hấp dẫn
                        </p>

                        <form action class="pb-4">
                            <div class="input-email">
                                <input type="email" name="email" id="email" placeholder="Nhập email của bạn"
                                    class="w-100">
                            </div>
                            <button class="w-100 bg-dark btn text-white mt-4 fw-bold register-end mb-4">ĐĂNG KÍ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Làm chuyển động slide chậm hơn */
.carousel-inner .carousel-item {
    transition: transform 1.3s ease-in-out;
}

/* Thu nhỏ nút điều hướng */
.custom-prev,
.custom-next {
    width: 30px;
    height: 30px;
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 300px;
}

.custom-prev span,
.custom-next span {
    width: 20px;
    height: 20px;
}

/* Đổi màu nút điều hướng khi hover */
.custom-prev:hover,
.custom-next:hover {
    background-color: rgba(0, 0, 0, 0.8);
}
.favorite button svg {
    fill: transparent;
    stroke: #000; 
}

.favorite button.text-dark svg {
    fill: #000; 
    stroke: #000; 
}
</style>

@endsection