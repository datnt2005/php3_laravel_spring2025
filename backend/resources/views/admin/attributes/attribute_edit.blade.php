@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Attribute</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('attributes.update', $attribute->id) }}" class="m-5">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Attribute Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $attribute->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $attribute->slug) }}">
                        </div>
                        <div id="attribute-value-container" class="mb-3">
                            @foreach($attribute->values as $index => $value)
                                <div class="row" id="attribute-value-{{ $index }}">
                                    <div class="col-md-10">
                                        <label class="form-label">Attribute Values</label>
                                        <input type="text" name="attributes[{{ $index }}][value]" class="form-control" value="{{ $value->value }}" required>
                                    </div>

                                    <div class="col-md-2 mt-4">
                                        <button type="button" class="btn btn-danger" onclick="removeAttributeValue({{ $index }})">Xoá</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-info" id="addAttributeValueButton">Add Value</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const attributeContainer = document.getElementById('attribute-value-container');
    const addAttributeValueButton = document.getElementById('addAttributeValueButton');
    let attributeCount = {{ $attribute->values->count() }};

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

    function removeAttributeValue(index) {
        const valueRow = document.getElementById(`attribute-value-${index}`);
        if (valueRow) valueRow.remove();
    }

    addAttributeValueButton.addEventListener('click', addAttributeValue);
</script>
@endsection
