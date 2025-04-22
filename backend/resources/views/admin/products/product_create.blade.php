@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Product</h3>
                </div>
                <form method="POST" action="{{ route('products.create') }}" id="createProductForm" enctype="multipart/form-data" class="m-5">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categories" class="form-label">Category</label>
                        <select class="form-select" id="categories" name="categories[]" multiple required>
                            <option value="">--Danh mục--</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category->nameCategory }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">--Thương Hiệu--</option>
                            @foreach ($brands as $brand)
                            <option value="{{ $brand['id'] }}">{{ $brand->nameBrand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image[]" multiple required>
                        <div class="list-images mt-3" id="listImages"></div>
                    </div>

                    <!-- Variant Container -->
                    <div id="variant-container" class="mb-3"></div>
                    <button type="button" class="btn btn-info" id="addVariantButton">Thêm biến thể</button>
                    <button type="submit" class="btn btn-success">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const attributes = <?= json_encode($attributes) ?>;
    const variantContainer = document.getElementById('variant-container');
    const addVariantButton = document.getElementById('addVariantButton');
    let variantCount = 0;

    function addVariant() {
        const variantId = variantCount++;
        const variantHTML = `
            <div class="variant-group rounded p-3 mb-3" style="border: 2px solid #ccc;" id="variant-${variantId}">
                <div class="row">
                    <div class="col-md-12">
                        <label>Chọn thuộc tính</label>
                        <div class="attribute-container" id="attribute-container-${variantId}"></div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addAttribute(${variantId})">Thêm thuộc tính</button>
                    </div>
                    <div class="col-md-6">
                        <label>Giá</label>
                        <input type="number" name="variants[${variantId}][price]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Giá nhập</label>
                        <input type="number" name="variants[${variantId}][cost_price]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Giá khuyến mãi</label>
                        <input type="number" name="variants[${variantId}][sale_price]" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Số lượng</label>
                        <input type="number" name="variants[${variantId}][quantity]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Image</label>
                        <input type="file" name="variants[${variantId}][image]" class="form-control">
                    </div>
                    <div class="col-md-12 text-end mt-3">
                        <button type="button" class="btn btn-danger" onclick="removeVariant(${variantId})">Xóa biến thể</button>
                    </div>
                </div>
            </div>
        `;
        variantContainer.insertAdjacentHTML('beforeend', variantHTML);
        addAttribute(variantId); // Thêm 1 cặp thuộc tính mặc định
    }

    function addAttribute(variantId) {
        const attributeContainer = document.getElementById(`attribute-container-${variantId}`);
        const attrIndex = document.querySelectorAll(`#attribute-container-${variantId} .attribute-row`).length;

        let attributeOptions = attributes.map(attr => `<option value="${attr.id}">${attr.name}</option>`).join('');

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
            </div>
        `;
        attributeContainer.insertAdjacentHTML('beforeend', attributeHTML);
    }

    function loadValues(select, variantId, attrIndex) {
        const valueSelect = select.nextElementSibling;
        const attributeId = select.value;
        valueSelect.innerHTML = '<option value="">Chọn giá trị</option>';

        if (attributeId) {
            const selectedAttribute = attributes.find(attr => attr.id == attributeId);
            if (selectedAttribute) {
                selectedAttribute.values.forEach(value => {
                    valueSelect.innerHTML += `<option value="${value.id}">${value.value}</option>`;
                });
                valueSelect.removeAttribute('disabled');
            }
        } else {
            valueSelect.setAttribute('disabled', true);
        }
    }

    function removeVariant(index) {
        document.getElementById(`variant-${index}`).remove();
    }

    function removeAttribute(variantId, attrIndex) {
        const attributeRow = document.getElementById(`attr-row-${variantId}-${attrIndex}`);
        if (attributeRow) {
            attributeRow.remove();
        }
    }

    addVariantButton.addEventListener('click', addVariant);

    //hinh anh
const imageInput = document.getElementById('image');
const listImages = document.getElementById('listImages');

imageInput.addEventListener('change', function() {
    // Xóa các ảnh cũ đã hiển thị
    listImages.innerHTML = "";

    Array.from(imageInput.files).forEach((file, index) => {
        const imageUrl = URL.createObjectURL(file);
        const imageWrapper = document.createElement('div');
        imageWrapper.classList.add('image-wrapper', 'd-inline-block',
            'position-relative', 'me-2', 'mb-2');

        imageWrapper.innerHTML = `
                <img src="${imageUrl}" alt="Image Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="removeSelectedImage(${index})">&times;</button>
            `;
        listImages.appendChild(imageWrapper);
    });
});

function removeSelectedImage(index) {
    const files = Array.from(imageInput.files);
    files.splice(index, 1);

    // Tạo lại FileList từ danh sách mới
    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    imageInput.files = dataTransfer.files;

    // Cập nhật hiển thị
    const changeEvent = new Event("change");
    imageInput.dispatchEvent(changeEvent);
}
</script>
@endsection
