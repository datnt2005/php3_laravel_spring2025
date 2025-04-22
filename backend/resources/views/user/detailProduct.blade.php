@extends('layouts.appUser')
@section('content')
<main class="container my-4">
    <div class="container mt-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="nav-link">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
            </ol>
        </nav>
    </div>

    <div class="container mt-3">
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-md-5 me-2">
                <div class="image_product">
                    <!-- Ảnh sản phẩm chính -->
                    <div class="show_image">
                        @if ($product->productPic->isNotEmpty())
                            <img src="{{ asset('storage/' . $product->productPic->first()->imagePath) }}" alt="sanpham" id="main-image">
                        @elseif ($productVariants->isNotEmpty())
                            <img src="{{ asset('storage/' . $productVariants->first()->image) }}" alt="sanpham" id="main-image">
                        @else
                            <img src="{{ asset('images/no-image.png') }}" alt="Không có ảnh" id="main-image">
                        @endif
                    </div>

                    <!-- Danh sách ảnh nhỏ -->
                    <div class="image_thumbnail d-flex mt-2">
                        @foreach ($product->productPic as $image)
                            <div class="thumbnails me-2">
                                <img src="{{ asset('storage/' . $image->imagePath) }}" class="img-thumbnails thumbnail-image"
                                    onclick="updateMainImage('{{ asset('storage/' . $image->imagePath) }}')">
                            </div>
                        @endforeach
                        @foreach ($productVariants as $variant)
                            <div class="thumbnails me-2">
                                <img src="{{ asset('storage/' . $variant->image) }}" class="img-thumbnails thumbnail-image"
                                    onclick="updateMainImage('{{ asset('storage/' . $variant->image) }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-5 ms-3"> 
                <div class="product_content">
                    <form id="addToCartForm-{{ $product->id }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variant_id" id="variant-id-{{ $product->id }}">

                        <!-- Tên sản phẩm -->
                        <h3 class="fw-bold fs-5 my-1">{{ $product->name }}</h3>
                        <p class="product_id my-1 text-muted">MÃ SP: <span id="sku">
                            {{ $productVariants->isNotEmpty() ? $productVariants->first()->sku : 'N/A' }}
                        </span></p>
                        <div class="mt-3">
                            @if($minSalePrice !== null && $minSalePrice > 0)
                                <div class="d-flex">
                                    <p class="text-decoration-line-through">
                                        <span id="price">{{ number_format($minPrice) }}</span>
                                        <span class="fw-medium fs-7 text-decoration-underline">đ</span>
                                    </p>
                                    <p class="ms-2 fw-bold">
                                        <span id="sale_price">{{ number_format($minSalePrice) }}</span>
                                        <span class="fw-medium fs-7 text-decoration-underline">đ</span>
                                    </p>
                                </div>
                            @else
                                <p class="fw-bold">
                                    <span id="price">{{ number_format($minPrice) }}</span>
                                    <span class="fw-medium fs-7 text-decoration-underline">đ</span>
                                </p>
                            @endif
                        </div>

                        <!-- Các nhóm thuộc tính động -->
                        @if ($productVariants->isNotEmpty())
                            @foreach ($attributeGroups as $attrName => $attributes)
                                <?php
                                    $attrId = str_replace(' ', '_', \Illuminate\Support\Str::slug($attrName, '_'));
                                ?>
                                <div class="attribute-group mt-1">
                                    <label class="d-block fs-6 mb-2 fw-bold">{{ ucfirst($attrName) }}: <span class="errors text-danger" id="{{ $attrId }}-error-{{ $product->id }}"></span></label>
                                    <div class="button-group {{ $loop->first ? '' : 'd-none' }}" id="{{ $attrId }}-group-{{ $product->id }}">
                                        @foreach ($attributes as $attribute)
                                            <button type="button" 
                                                class="btn variant-btn rounded-0 {{ $attrName === 'color' ? 'color-btn' : 'size-btn' }}"
                                                data-attribute="{{ $attrName }}"
                                                data-value="{{ $attribute['value_id'] }}"
                                                data-product-variant-ids="{{ json_encode($attribute['variant_ids']) }}"
                                                onclick="selectAttribute('{{ $attrName }}', '{{ $attribute['value_id'] }}', '{{ $product->id }}')"
                                                @if($attrName === 'color') style="background-color: {{ $attribute['value'] === 'White' ? '#fff' : ($attribute['value'] === 'Black' ? '#000' : $attribute['value']) }}; color: {{ $attribute['value'] === 'White' ? '#000' : '#fff' }}; font-size: 0.6rem; padding-left:8px;" @endif>
                                                {{ $attribute['value'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="{{ $attrName }}" id="{{ $attrId }}-{{ $product->id }}">
                                </div>
                            @endforeach

                            <!-- Dữ liệu biến thể -->
                            <script id="variants-{{ $product->id }}" type="application/json">
                                {!! json_encode($productVariants->map(function ($variant) {
                                    $attributes = $variant->attributes->pluck('value_id', 'attribute.name')->all();
                                    return array_merge([
                                        'id' => $variant->idVariant,
                                        'sku' => $variant->sku,
                                        'price' => $variant->price,
                                        'sale_price' => $variant->sale_price > 0 ? $variant->sale_price : 0,
                                        'quantityProduct' => $variant->quantityProduct,
                                        'image' => $variant->image ? asset('storage/' . $variant->image) : '',
                                    ], $attributes);
                                })) !!}
                            </script>
                            <script id="attribute-order-{{ $product->id }}" type="application/json">
                                {!! json_encode($attributeOrder) !!}
                            </script>
                        @endif

                        <!-- Số lượng -->
                        <div class="quantity mt-3 mb-3">
                            <button type="button" class="prev" onclick="updateQuantity(-1)">-</button>
                            <input type="text" class="quantity-cart" name="quantity-cart" value="1">
                            <button type="button" class="pluss" onclick="updateQuantity(1)">+</button>
                        </div>
                        <span class="errors text-danger" id="quantity-error-{{ $product->id }}"></span>
                        <p class="fw-bold mb-3">Kho: <span id="quantityProduct">
                            {{ $productVariants->isNotEmpty() ? $productVariants->first()->quantityProduct : 'N/A' }}
                        </span></p>

                        <!-- Banner freeship -->
                        <div class="image_freeship">
                            <img src="https://owen.cdn.vccloud.vn/media/amasty/ampromobanners/CD06C467-DE0F-457E-9AB0-9D90B567E118.jpeg" alt="Freeship" class="w-100">
                        </div>

                        <!-- Nút thêm vào giỏ hàng -->
                        <button type="button" class="btn btn-dark w-100 mt-5 fw-bold rounded-0" onclick="addToCart('{{ $product->id }}')">
                            Thêm vào giỏ hàng
                        </button>
                    </form>

                    <div class="description mt-5">
                        <span class="description_heading fw-bold">MÔ TẢ</span>
                        <hr class="m-0">
                        <p class="fs-7 mt-1">{{ $product->description }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-1"></div>
        </div>
    </div>

    <div id="alerts-container"></div>
</main>

<style>
    .text-danger { color: red; }
    .text-decoration-line-through { text-decoration: line-through; }
    .img-thumbnails { width: 70px; height: 70px; cursor: pointer; border: 2px solid transparent; }
    .img-thumbnails:hover, .selected-thumbnail { border-color: #000; }
    .variant-btn { margin-right: 5px; border: 1px solid #ddd; padding: 5px 10px; min-width: 40px; text-align: center; }
    .variant-btn:hover, .variant-btn.active { color: rgb(68, 68, 68); background-color: #e9ecef; }
    .alert { position: fixed; top: 115px; left: 45%; z-index: 1000; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); }
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const mainImage = document.getElementById("main-image");
    const skuElement = document.getElementById("sku");
    const priceElement = document.getElementById("price");
    const salePriceElement = document.getElementById("sale_price"); // Có thể là null nếu không có giá giảm
    const quantityElement = document.getElementById("quantityProduct");

    let selectedAttributes = {};
    const attributeOrder = JSON.parse(document.getElementById(`attribute-order-{{ $product->id }}`).textContent);

    function selectAttribute(attributeName, valueId, productId) {
        selectedAttributes[attributeName] = valueId;

        const attrId = attributeName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ /g, "_");
        document.getElementById(`${attrId}-${productId}`).value = valueId;

        const groupSelector = `#${attrId}-group-${productId}`;
        document.querySelectorAll(`${groupSelector} .variant-btn`).forEach(btn => {
            btn.classList.remove("active");
        });

        const buttonSelector = `${groupSelector} .variant-btn[data-value="${valueId}"]`;
        const selectedButton = document.querySelector(buttonSelector);
        if (!selectedButton) {
            console.error(`Không tìm thấy nút cho attributeName: ${attributeName}, valueId: ${valueId}`);
            console.log("Selector đã sử dụng:", buttonSelector);
            console.log("HTML nhóm thuộc tính:", document.querySelector(groupSelector)?.innerHTML || "Không tìm thấy nhóm");
            return;
        }
        selectedButton.classList.add("active");

        const productVariantIds = JSON.parse(selectedButton.getAttribute("data-product-variant-ids"));
        const currentIndex = attributeOrder.indexOf(attributeName);

        attributeOrder.slice(currentIndex + 1).forEach(nextAttr => {
            const nextAttrId = nextAttr.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ /g, "_");
            const nextGroup = document.getElementById(`${nextAttrId}-group-${productId}`);
            if (nextGroup) {
                nextGroup.classList.add("d-none");
            }
            document.getElementById(`${nextAttrId}-${productId}`).value = "";
            delete selectedAttributes[nextAttr];
        });

        if (currentIndex < attributeOrder.length - 1) {
            const nextAttr = attributeOrder[currentIndex + 1];
            const nextAttrId = nextAttr.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ /g, "_");
            const nextGroup = document.getElementById(`${nextAttrId}-group-${productId}`);
            if (nextGroup) {
                nextGroup.classList.remove("d-none");
                document.querySelectorAll(`#${nextAttrId}-group-${productId} .variant-btn`).forEach(btn => {
                    btn.style.display = "inline-block";
                    const variantIds = JSON.parse(btn.getAttribute("data-product-variant-ids"));
                    const isValid = variantIds.some(id => productVariantIds.includes(id));
                    if (!isValid) {
                        btn.style.display = "none";
                    }
                });
            }
        }

        const variant = findVariant(productId);
        if (variant) {
            skuElement.innerText = variant.sku;
            priceElement.innerText = new Intl.NumberFormat().format(variant.price);
            // Kiểm tra salePriceElement trước khi sử dụng
            if (salePriceElement) {
                if (variant.sale_price > 0) {
                    salePriceElement.parentElement.style.display = "block";
                    salePriceElement.innerText = new Intl.NumberFormat().format(variant.sale_price);
                } else {
                    salePriceElement.parentElement.style.display = "none";
                }
            }
            quantityElement.innerText = variant.quantityProduct;
            mainImage.src = variant.image || mainImage.src;
            document.getElementById(`variant-id-${productId}`).value = variant.id;
            console.log("Variant ID updated:", variant.id); // Debug
        } else {
            console.log("Không tìm thấy biến thể phù hợp với:", selectedAttributes);
        }
    }

    function findVariant(productId) {
        const variants = JSON.parse(document.getElementById(`variants-${productId}`).textContent);
        const matchedVariant = variants.find(variant => {
            return Object.keys(selectedAttributes).every(attr => variant[attr] == selectedAttributes[attr]);
        });
        return matchedVariant;
    }

    window.selectAttribute = selectAttribute;
});

function updateMainImage(imagePath) {
    document.getElementById("main-image").src = imagePath;
}

function updateQuantity(change) {
    const quantityInput = document.querySelector(".quantity-cart");
    let currentQuantity = parseInt(quantityInput.value) || 1;
    currentQuantity += change;
    if (currentQuantity < 1) currentQuantity = 1;
    quantityInput.value = currentQuantity;
}

function showAlert(message, type = "danger") {
    const alertContainer = document.getElementById("alerts-container");
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertContainer.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

function addToCart(productId) {
    const attributes = {};
    document.querySelectorAll(`#addToCartForm-${productId} input[type="hidden"]:not([name="product_id"]):not([name="variant_id"]):not([name="_token"])`).forEach(input => {
        attributes[input.name] = input.value;
    });
    const quantity = parseInt(document.querySelector(".quantity-cart").value);
    const availableQuantity = parseInt(document.getElementById("quantityProduct").innerText);

    for (let [attrName, value] of Object.entries(attributes)) {
        const attrId = attrName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ /g, "_");
        if (!value) {
            showAlert(`Vui lòng chọn ${attrName}!`);
            document.getElementById(`${attrId}-error-${productId}`).textContent = `Vui lòng chọn ${attrName}`;
            return;
        } else {
            document.getElementById(`${attrId}-error-${productId}`).textContent = "";
        }
    }

    if (quantity > availableQuantity) {
        showAlert(`Số lượng tồn kho không đủ! Chỉ còn ${availableQuantity} sản phẩm.`);
        document.getElementById(`quantity-error-${productId}`).textContent = `Số lượng tồn kho không đủ! Chỉ còn ${availableQuantity} sản phẩm.`;
        return;
    } else {
        document.getElementById(`quantity-error-${productId}`).textContent = "";
    }

    const form = document.getElementById(`addToCartForm-${productId}`);
    const formData = new FormData(form);
    console.log("Form data:", Object.fromEntries(formData)); // Debug dữ liệu gửi lên
    fetch("{{ route('cart.add') }}", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json",
        },
    })
    .then(response => {
        console.log("Response status:", response.status); // Debug mã trạng thái
        return response.json();
    })
    .then(data => {
        console.log("Response data:", data); // Debug phản hồi từ server
        if (data.success) {
            showAlert("Đã thêm sản phẩm vào giỏ hàng!", "success");
            const cartCountElement = document.getElementById("cart-count");
            if (cartCountElement) {
                cartCountElement.textContent = data.cartTotalQuantity;
            }
        } else {
            const errorMessage = data.errors ? Object.values(data.errors).join(", ") : (data.message || "Có lỗi xảy ra khi thêm vào giỏ hàng!");
            showAlert(errorMessage);
        }
    })
    .catch(error => {
        showAlert("Có lỗi xảy ra khi thêm vào giỏ hàng!");
        console.error("Error:", error);
    });
}
</script>
@include('user._reviews', ['comments' => $comments, 'product' => $product])

<div class="container mt-5">
    <div class="row">
        <h4 class="fw-bold">CÓ THỂ BẠN THÍCH</h4>
        @forelse ($productRelated as $product)
            <div class="col-md-3 mt-3">
                <div class="products-item">
                    <div class="image-product">
                        <a href="{{ url('/product/' . $product->slug) }}" class="image-product-links">
                            @if ($product->productPic->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->productPic->first()->imagePath) }}" alt="sanpham">
                            @elseif ($product->productVariants->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->productVariants->first()->image) }}" alt="sanpham">
                            @else
                                <img src="{{ asset('images/no-image.png') }}" alt="Không có ảnh">
                            @endif
                        </a>
                        <a href="{{ url('/product/' . $product->slug) }}">
                            <button type="button" class="product-button border-0 add_cart_btn text-center text-decoration-none py-2 btn_add--checkout px-2" data-product-id="{{ $product->id }}">
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
                        <a href="{{ url('/product/' . $product->slug) }}" class="text-secondary text-uppercase fw-bold text-decoration-none">
                            {{ $product->name }}
                        </a>
                    </div>
                    <div class="price-product fw-bold">
                        @if(!empty($product->productVariants[0]->sale_price) && $product->productVariants[0]->sale_price > 0)
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
    </div>

@endsection