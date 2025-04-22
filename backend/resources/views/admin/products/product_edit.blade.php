@extends('layouts.appAdmin')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Product</h3>
                </div>
                <form method="POST" action="{{ route('products.update', $product->id) }}" id="updateProductForm"
                    enctype="multipart/form-data" class="m-5">
                    @csrf
                    @method('PUT')

                    <!-- Hiển thị thông báo lỗi nếu có -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Tên sản phẩm -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}"
                            required>
                    </div>

                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $product->slug) }}">
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            required>{{ old('description', $product->description) }}</textarea>
                    </div>

                    <!-- Danh mục -->
                    <div class="mb-3">
                        <label for="categories" class="form-label">Category</label>
                        <select class="form-select" id="categories" name="categories[]" multiple required>
                            <option value="">--Danh mục--</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ in_array($category->id, old('categories', $product->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $category->nameCategory }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Thương hiệu -->
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">--Thương Hiệu--</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->nameBrand }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Hình ảnh sản phẩm -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Images</label>
                        <input type="file" class="form-control" id="image" name="image[]" multiple>
                        <div class="list-images mt-3" id="listImages">
                            @foreach($product->productPic as $image)
                                <div class="image-wrapper position-relative d-inline-block me-2 mb-2">
                                    <img src="{{ asset('storage/' . $image->imagePath) }}" alt="Image" class="img-thumbnail"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                        onclick="removeExistingImage({{ $image->id }}, this)">×</button>
                                    <input type="hidden" name="old_images[]" value="{{ $image->id }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Biến thể sản phẩm -->
                    <div id="variant-container" class="mb-3">
                        @foreach ($product->productVariants as $index => $variant)
                            <div class="variant-group rounded p-3 mb-3" style="border: 2px solid #ccc;" id="variant-{{ $index }}">
                                <div class="row">
                                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                    <div class="col-md-12">
                                        <label>Chọn thuộc tính</label>
                                        <div class="attribute-container" id="attribute-container-{{ $index }}">
                                            @foreach ($variant->attributes as $attrIndex => $variantAttribute)
                                                <div class="attribute-row d-flex align-items-center mt-2"
                                                    id="attr-row-{{ $index }}-{{ $attrIndex }}">
                                                    <select name="variants[{{ $index }}][attributes][{{ $attrIndex }}][attribute_id]"
                                                        class="form-select me-2" onchange="loadValues(this, {{ $index }}, {{ $attrIndex }})">
                                                        <option value="">Chọn thuộc tính</option>
                                                        @foreach ($attributes as $attribute)
                                                            <option value="{{ $attribute->id }}"
                                                                {{ $variantAttribute->attribute_id == $attribute->id ? 'selected' : '' }}>
                                                                {{ $attribute->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <select name="variants[{{ $index }}][attributes][{{ $attrIndex }}][value_id]"
                                                        class="form-select me-2">
                                                        <option value="">Chọn giá trị</option>
                                                        @foreach ($attributes->find($variantAttribute->attribute_id)->values ?? [] as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ $variantAttribute->value_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="removeAttribute({{ $index }}, {{ $attrIndex }})">X</button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-secondary mt-2 mb-3"
                                            onclick="addAttribute({{ $index }})">Thêm thuộc tính</button>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price-{{ $index }}" class="form-label">Price</label>
                                            <input type="number" name="variants[{{ $index }}][price]" id="price-{{ $index }}"
                                                class="form-control" min="0" value="{{ old("variants.$index.price", $variant->price) }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="cost_price-{{ $index }}" class="form-label">Cost Price</label>
                                            <input type="number" name="variants[{{ $index }}][cost_price]" id="cost_price-{{ $index }}"
                                                class="form-control" min="0" value="{{ old("variants.$index.cost_price", $variant->cost_price) }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sale_price-{{ $index }}" class="form-label">Sale Price</label>
                                            <input type="number" name="variants[{{ $index }}][sale_price]" id="sale_price-{{ $index }}"
                                                class="form-control" min="0" value="{{ old("variants.$index.sale_price", $variant->sale_price) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="quantity-{{ $index }}" class="form-label">Quantity</label>
                                            <input type="number" name="variants[{{ $index }}][quantity]" id="quantity-{{ $index }}"
                                                class="form-control" min="0" value="{{ old("variants.$index.quantity", $variant->quantityProduct) }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sku-{{ $index }}" class="form-label">SKU</label>
                                            <input type="text" name="variants[{{ $index }}][sku]" id="sku-{{ $index }}"
                                                class="form-control" value="{{ old("variants.$index.sku", $variant->sku) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="image-{{ $index }}" class="form-label">Image</label>
                                            <input type="file" name="variants[{{ $index }}][image]" id="image-{{ $index }}"
                                                class="form-control" >
                                            @if($variant->image)
                                                <img src="{{ asset('storage/' . $variant->image) }}" alt="Variant Image"
                                                    class="img-thumbnail mt-2" style="width: 80px; height: 80px; object-fit: cover;">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-end mt-3">
                                        <button type="button" class="btn btn-danger"
                                            onclick="removeVariant({{ $index }})">Xóa biến thể</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Nút thêm biến thể và submit -->
                    <button type="button" class="btn btn-info" id="addVariantButton">Thêm biến thể</button>
                    <button type="submit" class="btn btn-success">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const attributes = @json($attributes);
    const variantContainer = document.getElementById('variant-container');
    const addVariantButton = document.getElementById('addVariantButton');
    const imageInput = document.getElementById('image');
    const listImages = document.getElementById('listImages');
    let variantCount = {{ $product->productVariants->count() }};

    // Thêm biến thể mới
    function addVariant() {
        const variantId = variantCount++;
        const variantHTML = `
            <div class="variant-group rounded p-3 mb-3" style="border: 2px solid #ccc;" id="variant-${variantId}">
                <div class="row">
                    <div class="col-md-12">
                        <label>Chọn thuộc tính</label>
                        <div class="attribute-container" id="attribute-container-${variantId}"></div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2 mb-3" onclick="addAttribute(${variantId})">Thêm thuộc tính</button>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Giá</label>
                            <input type="number" name="variants[${variantId}][price]" class="form-control" placeholder="Nhập giá" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>Giá nhập</label>
                            <input type="number" name="variants[${variantId}][cost_price]" class="form-control" placeholder="Nhập giá nhập" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>Giá khuyến mãi</label>
                            <input type="number" name="variants[${variantId}][sale_price]" class="form-control" placeholder="Nhập giá khuyến mãi" min="0">
                        </div>
                        <div class="mb-3">
                            <label>Số lượng</label>
                            <input type="number" name="variants[${variantId}][quantity]" class="form-control" placeholder="Nhập số lượng" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>SKU</label>
                            <input type="text" name="variants[${variantId}][sku]" class="form-control" placeholder="Nhập SKU" >
                        </div>
                        <div class="mb-3">
                            <label>Image</label>
                            <input type="file" name="variants[${variantId}][image]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12 text-end mt-3">
                        <button type="button" class="btn btn-danger" onclick="removeVariant(${variantId})">Xóa biến thể</button>
                    </div>
                </div>
            </div>`;
        variantContainer.insertAdjacentHTML('beforeend', variantHTML);
        addAttribute(variantId); // Thêm thuộc tính mặc định cho biến thể mới
    }

    // Thêm thuộc tính mới
    function addAttribute(variantId) {
        const attributeContainer = document.getElementById(`attribute-container-${variantId}`);
        const attrIndex = attributeContainer.querySelectorAll('.attribute-row').length;
        const attributeOptions = attributes.map(attr => `<option value="${attr.id}">${attr.name}</option>`).join('');
        const attributeHTML = `
            <div class="attribute-row d-flex align-items-center mt-2" id="attr-row-${variantId}-${attrIndex}">
                <select name="variants[${variantId}][attributes][${attrIndex}][attribute_id]" class="form-select me-2" onchange="loadValues(this, ${variantId}, ${attrIndex})">
                    <option value="">Chọn thuộc tính</option>
                    ${attributeOptions}
                </select>
                <select name="variants[${variantId}][attributes][${attrIndex}][value_id]" class="form-select me-2" disabled>
                    <option value="">Chọn giá trị</option>
                </select>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeAttribute(${variantId}, ${attrIndex})">X</button>
            </div>`;
        attributeContainer.insertAdjacentHTML('beforeend', attributeHTML);
    }

    // Tải giá trị thuộc tính
    function loadValues(select, variantId, attrIndex) {
        const valueSelect = select.nextElementSibling;
        const attributeId = select.value;

        valueSelect.innerHTML = '<option value="">Chọn giá trị</option>';
        valueSelect.disabled = true;

        if (attributeId) {
            const selectedAttribute = attributes.find(attr => attr.id == attributeId);
            if (selectedAttribute && selectedAttribute.values) {
                selectedAttribute.values.forEach(value => {
                    valueSelect.innerHTML += `<option value="${value.id}">${value.value}</option>`;
                });
                valueSelect.disabled = false;
            }
        }
    }

    // Xóa biến thể
    function removeVariant(index) {
        const variantElement = document.getElementById(`variant-${index}`);
        if (variantElement) {
            variantElement.remove();
        }
    }

    // Xóa thuộc tính
    function removeAttribute(variantId, attrIndex) {
        const attributeRow = document.getElementById(`attr-row-${variantId}-${attrIndex}`);
        if (attributeRow) {
            attributeRow.remove();
        }
    }

    // Xử lý hình ảnh mới
    imageInput.addEventListener('change', function() {
        Array.from(imageInput.files).forEach(file => {
            const imageUrl = URL.createObjectURL(file);
            const imageWrapper = document.createElement('div');
            imageWrapper.classList.add('image-wrapper', 'd-inline-block', 'position-relative', 'me-2', 'mb-2');
            imageWrapper.innerHTML = `
                <img src="${imageUrl}" alt="Image Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="removeNewImage(this)">×</button>`;
            listImages.appendChild(imageWrapper);
        });
    });

    // Xóa hình ảnh mới
    function removeNewImage(button) {
        button.parentNode.remove();
    }

    // Xóa hình ảnh cũ
    function removeExistingImage(imageId, button) {
        const imageWrapper = button.parentNode;
        imageWrapper.style.display = 'none';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_images[]';
        input.value = imageId;
        listImages.appendChild(input);
    }

    // Gắn sự kiện cho nút thêm biến thể
    addVariantButton.addEventListener('click', addVariant);
</script>
@endsection