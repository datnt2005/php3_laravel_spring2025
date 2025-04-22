@extends('layouts.appUser')
@section('content')
<main>
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html" class="nav-link">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Danh mục</li>
            </ol>
        </nav>
        <div class="row banner">
            <img src="https://owen.vn/media/catalog/category/4546_x_1000_ao_1.jpg" alt="banner">
        </div>
    </div>

    <div class="container">
        <div class="row">
            <aside class="col-md-2">
                <div class="sidebar">
                    <div class="sidebar-product-general mt-4">
                        <ul>
                            <li class="category-item"><a href="{{ route('shop.index') }}" class="fw-bold fs-6">DANH
                                    MỤC</a></li>
                        </ul>
                        <ul class="category-list">
                            @foreach ($categories as $category)
                            <li class="category-item">
                                <a href="{{ route('shop.index', ['category' => $category->slug]) }}">
                                    {{ $category->nameCategory }}
                                    @if($category->children->isNotEmpty()) <i class="fas fa-chevron-right"></i> @endif
                                </a>

                                @if($category->children->isNotEmpty())
                                <ul class="subcategory-list">
                                    @foreach ($category->children as $subCategory)
                                    <li class="subcategory-item">
                                        <a href="{{ route('shop.index', ['category' => $subCategory->slug]) }}">
                                            {{ $subCategory->nameCategory }}
                                            @if($subCategory->children->isNotEmpty()) <i
                                                class="fas fa-chevron-right"></i> @endif
                                        </a>

                                        @if($subCategory->children->isNotEmpty())
                                        <ul class="sub-subcategory-list">
                                            @foreach ($subCategory->children as $subSubCategory)
                                            <li class="sub-subcategory-item">
                                                <a
                                                    href="{{ route('shop.index', ['category' => $subSubCategory->slug]) }}">
                                                    {{ $subSubCategory->nameCategory }}
                                                    @if($subSubCategory->children->isNotEmpty()) <i
                                                        class="fas fa-chevron-right"></i> @endif
                                                </a>

                                                @if($subSubCategory->children->isNotEmpty())
                                                <ul class="sub-sub-subcategory-list">
                                                    @foreach ($subSubCategory->children as $subSubSubCategory)
                                                    <li class="sub-sub-subcategory-item">
                                                        <a
                                                            href="{{ route('shop.index', ['category' => $subSubSubCategory->slug]) }}">
                                                            {{ $subSubSubCategory->nameCategory }}
                                                        </a>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>




                        <ul class="mt-4">
                            <li class="category-item"><a href="{{ route('shop.index') }}" class="fw-bold fs-6">THƯƠNG
                                    HIỆU</a></li>
                        </ul>

                        <ul>
                            @foreach ($brands as $brand)
                            <li class="category-item">
                                <a
                                    href="{{ route('shop.index', array_merge(request()->except('brand'), ['brand' => $brand->id])) }}">
                                    {{ $brand->nameBrand }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <ul class="mt-4">
                            <li class="category-item"><a href="shop.php?view=Dog_products" class="fw-bold fs-6">GIÁ
                                    TỐT</a></li>
                            <li class="category-item"><a href="shop.php?view=Food_products" class>Polo - Tshirt</a></li>
                            <li class="category-item"><a href="shop.php?view=Cat_products" class>Quần giá tốt</a></li>
                            <li class="category-item"><a href="shop.php?view=Bird_products" class>Áo sơ mi</a></li>
                        </ul>
                        <ul class="mt-4">
                            <li class="category-item"><a href="#" class="fw-bold fs-6">GIÁ</a></li>
                            @foreach ($priceRanges as $range)
                            <li class="category-item">
                                <a
                                    href="{{ route('shop.index', array_merge(request()->except('price'), ['price' => $range['min'] . '-' . $range['max']])) }}">
                                    {{ $range['label'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </aside>
            <div class="col-md-10">
                <div class="container">

                    <button class="p-1 mt-2 mb-2" type="button" id="toggleFilters">
                        Bộ lọc <i class="fa-solid fa-sliders"></i>
                    </button>
                    <div class="row">

                        @forelse ($products as $product)
                        <div class="col-md-4 mb-4">
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
                                            class="bg-transparent border-0 p-0 {{ in_array($product->id, $favoriteIds) ? 'text-dark' : 'text-white border border-dark' }}"
                                            title="{{ in_array($product->id, $favoriteIds) ? 'Bỏ yêu thích' : 'Thêm yêu thích' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                                                viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
                                                stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-heart">
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
                        @empty
                        <p>Sản phẩm không tồn tại trong cửa hàng.</p>
                        @endforelse
                    </div>
                    {{ $products->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
    <div id="filtersModal" class="filters-modal d-none">
        <div class="filters-modal-content px-5">
            <h5 class="text-center mb-3">Bộ lọc</h5>
            <button id="closeFilters" class="close-button btn btn-transparent "
                style="position: absolute; top: 10px; right: 10px;">✖️</button>
            <!-- Danh mục -->
            <div class="mb-3">
                <h6>Danh mục</h6>
                @foreach ($categories as $category)
                <div>
                    <input type="checkbox" id="category_{{ $category->slug }}" name="category[]"
                        value="{{ $category->slug }}">
                    <label for="category_{{ $category->id }}">{{ $category->nameCategory }}</label>
                </div>
                @endforeach
            </div>
            <!-- thể loại -->
            <div class="mb-3">
                <h6>Thuộc tính</h6>
                <div class="container">
                    <div class="row">
                        @foreach ($attributes as $attribute)
                        <div class="col-md-6">
                            <p class="fw-bold mb-0">{{ $attribute->name }}</p>
                            <div class="mt-0">
                                @foreach ($attribute->values as $value)
                                <div>
                                    <input type="checkbox" id="attribute_{{ $attribute->id }}_{{ $value->id }}"
                                        name="attribute[]" value="{{ $value->id }}">
                                    <label
                                        for="attribute_{{ $attribute->id }}_{{ $value->id }}">{{ $value->value }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
            <!-- Hãng -->
            <div class="mb-3">
                <h6>Hãng</h6>
                @foreach ($brands as $brand)
                <div>
                    <input type="checkbox" id="brand_{{ $brand->id }}" name="brand[]" value="{{ $brand->id }}">
                    <label for="brand_{{ $brand->id }}">{{ $brand->nameBrand }}</label>
                </div>
                @endforeach
            </div>


            <!-- Khoảng giá -->
            <div class="mb-3">
                <h6>Khoảng giá</h6>
                <div>
                    <input type="radio" id="price_1" name="price" value="0-100000">
                    <label for="price_1">Dưới 100.000</label>
                </div>
                <div>
                    <input type="radio" id="price_2" name="price" value="100000-200000">
                    <label for="price_2">100.000 - 200.000</label>
                </div>
                <div>
                    <input type="radio" id="price_3" name="price" value="200000-300000">
                    <label for="price_3">200.000 - 300.000</label>
                </div>
                <div>
                    <input type="radio" id="price_4" name="price" value="300000-500000">
                    <label for="price_4">300.000 - 500.000</label>
                </div>
                <div>
                    <input type="radio" id="price_5" name="price" value="500000-1000000">
                    <label for="price_5">500.000 - 1.000.000</label>
                </div>
                <div>
                    <input type="radio" id="price_6" name="price" value="1000000-">
                    <label for="price_6">Trên 1.000.000</label>
                </div>
            </div>

            <div class=" mt-3">
                <button id="applyFilters" class="btn">Áp dụng</button>
            </div>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFiltersButton = document.getElementById('toggleFilters');
    const filtersModal = document.getElementById('filtersModal');
    const closeFiltersButton = document.getElementById('closeFilters');
    const applyFiltersButton = document.getElementById('applyFilters');

    // Hiển thị modal bộ lọc
    if (toggleFiltersButton) {
        toggleFiltersButton.addEventListener('click', function() {
            filtersModal.classList.remove('d-none');
        });
    }

    // Đóng modal bộ lọc
    if (closeFiltersButton) {
        closeFiltersButton.addEventListener('click', function() {
            filtersModal.classList.add('d-none');
        });
    }

    // Áp dụng bộ lọc
    if (applyFiltersButton) {
        applyFiltersButton.addEventListener('click', function() {
            let selectedCategories = Array.from(document.querySelectorAll(
                'input[name="category[]"]:checked')).map(el => el.value);
            let selectedBrands = Array.from(document.querySelectorAll('input[name="brand[]"]:checked'))
                .map(el => el.value);
            let selectedAttributes = Array.from(document.querySelectorAll(
                'input[name="attribute[]"]:checked')).map(el => el.value);
            let selectedPrice = document.querySelector('input[name="price"]:checked')?.value;

            let url = new URL(window.location.href);

            // Xóa tất cả các tham số hiện tại
            url.searchParams.delete('category');
            url.searchParams.delete('brand');
            url.searchParams.delete('attribute');
            url.searchParams.delete('price');

            // Thêm các tham số lọc vào URL nếu có giá trị
            if (selectedCategories.length > 0) url.searchParams.set('category', selectedCategories.join(
                ','));
            if (selectedBrands.length > 0) url.searchParams.set('brand', selectedBrands.join(','));
            if (selectedAttributes.length > 0) url.searchParams.set('attribute', selectedAttributes
                .join(','));
            if (selectedPrice) url.searchParams.set('price', selectedPrice);

            // Chuyển hướng đến URL mới
            window.location.href = url.toString();
        });
    }
});
</script>
<style>

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 200px;

}

.category-item {
    position: relative;
}

.category-item>a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    text-decoration: none;
    font-weight: 600;
    background: rgb(253, 253, 253);
    border-bottom: 1px solid #ddd;
    transition: all 0.3s ease-in-out;
    border-radius: 3px;
}

.category-item>a:hover {
    color: rgb(189, 135, 74);
}

.subcategory-list,
.sub-subcategory-list {
    list-style: none;
    position: absolute;
    left: 100%;
    top: 0;
    display: none;
    min-width: 220px;
    background: white;
    box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.1);
    padding: 0;
    border-radius: 5px;
    z-index: 100;

}

.subcategory-item,
.sub-subcategory-item {
    position: relative;
}

.subcategory-item>a,
.sub-subcategory-item>a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease-in-out;
}

.subcategory-item>a:hover,
.sub-subcategory-item>a:hover {
    color: rgb(189, 135, 74);
}

.category-item:hover>.subcategory-list,
.subcategory-item:hover>.sub-subcategory-list {
    display: block;
}

.subcategory-list,
.sub-subcategory-list {
    opacity: 0;
    transform: translateX(-10px);
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
}

.category-item:hover>.subcategory-list,
.subcategory-item:hover>.sub-subcategory-list {
    opacity: 1;
    transform: translateX(0);
}

.fas {
    font-size: 10px;
    transition: transform 0.3s;
}

.category-item:hover>a .fas,
.subcategory-item:hover>a .fas {
    transform: rotate(90deg);
}

.sub-sub-subcategory-item {
    padding-left: 10px;
    list-style: none;
}
</style>
@endsection