@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Attribute</h3>
                </div>
                <div class="card-body">
                    {{-- Form nhập liệu --}}
                    <form method="POST" action="{{ route('attributes.create') }}" class="m-5">
                        @csrf  {{-- Bảo mật form --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Attribute Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div id="attribute-value-container" class="mb-3">
                            <!-- Nơi chứa các giá trị thuộc tính -->
                        </div>
                        <button type="button" class="btn btn-info" id="addAttributeValueButton">Add Value</button>
                        <button type="submit" class="btn btn-success">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    
    const attributeContainer = document.getElementById('attribute-value-container');
    const addAttributeValueButton = document.getElementById('addAttributeValueButton');
    let attributeCount = 0;

    // Thêm một giá trị thuộc tính mới
    function addAttributeValue() {
        const attributeValueHTML = `
            <div class="row" id="attribute-value-${attributeCount}">
                <div class="col-md-10">
                    <label class="form-label">Attribute Values</label>

                    <input type="text" name="attributes[${attributeCount}][value]" class="form-control" required>
                </div>
                <div class="col-md-2 mt-4">
                    <button type="button" class="btn btn-danger" onclick="removeAttributeValue(${attributeCount})">Xoá</button>
                </div>
            </div>
        `;
        attributeContainer.insertAdjacentHTML('beforeend', attributeValueHTML);
        attributeCount++;
    }

    // Xóa một giá trị thuộc tính
    function removeAttributeValue(index) {
        const valueRow = document.getElementById(`attribute-value-${index}`);
        if (valueRow) valueRow.remove();
    }

    // Gắn sự kiện click cho nút "Add Value"
    addAttributeValueButton.addEventListener('click', addAttributeValue);
</script>
@endsection