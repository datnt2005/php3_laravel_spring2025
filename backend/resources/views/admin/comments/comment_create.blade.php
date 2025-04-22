@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Comment</h3>
                </div>
                <form method="POST" class="m-5" action="{{ route('comment.create') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">User</label>
                        <select name="user_id" id="user" class="form-select" required>
                            <option value="">--User--</option>
                            @foreach ($users as $user)
                                @if ($user->role == 'admin')
                                        <option value="{{ $user->id }}">{{ $user->name }} (#{{ $user->id }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Product</label>
                        <select name="product_id" id="product" class="form-select" required>
                            <option value="">--Product--</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}
                                (#{{ $product->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <input type="text" class="form-control m-0" id="content" name="content" required>
                    </div>
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select name="rating" id="rating" class="form-select w-25" required>
                            <option value="">--Rating--</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Media</label>
                        <input type="file" class="form-control w-25" id="image" name="image[]" multiple >
                        <div class="list-images mt-3" id="listImages"></div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select w-25" required>
                            <option value="">--Status--</option>
                            <option value="visible">Visible</option>
                            <option value="hidden">Hidden</option>
                            <option value="reported">reported</option>

                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
const imageInput = document.getElementById('image');
const listImages = document.getElementById('listImages');

imageInput.addEventListener('change', function() {
    // Xóa các ảnh cũ đã hiển thị
    listImages.innerHTML = "";

    Array.from(imageInput.files).forEach((file, index) => {
        const imageUrl = URL.createObjectURL(file);
        const imageWrapper = document.createElement('div');
        imageWrapper.classList.add('image-wrapper', 'd-inline-block', 'position-relative', 'me-2', 'mb-2');

        imageWrapper.innerHTML = `
            <img src="{{ asset('storage') }}/${file.name}" alt="Image Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
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