@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Comment</h3>
                </div>
                <form method="POST" action="{{ route('comment.update', $comment->id) }}" class="m-5" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Sử dụng PUT để cập nhật -->
                    <div class="mb-3">
                        <label for="user" class="form-label">User</label>
                        <select name="user_id" id="user" class="form-select" required>
                            <option value="">--User--</option>
                            @foreach ($users as $user)
                                @if ($user->role == 'admin')
                                 <option value="{{ $user->id }}" {{ $user->id == $comment->user_id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                                @endif
                               
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product" class="form-label">Product</label>
                        <select name="product_id" id="product" class="form-select" required>
                            <option value="">--Product--</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ $product->id == $comment->product_id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <input type="text" class="form-control m-0" id="content" name="content" required 
                               value="{{ $comment->content }}">
                    </div>
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select name="rating" id="rating" class="form-select w-25" required>
                            <option value="">--Rating--</option>
                            <option value="1" {{ $comment->rating == 1 ? 'selected' : '' }}>1</option>
                            <option value="2" {{ $comment->rating == 2 ? 'selected' : '' }}>2</option>
                            <option value="3" {{ $comment->rating == 3 ? 'selected' : '' }}>3</option>
                            <option value="4" {{ $comment->rating == 4 ? 'selected' : '' }}>4</option>
                            <option value="5" {{ $comment->rating == 5 ? 'selected' : '' }}>5</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control w-25" id="image" name="image[]" multiple>
                        <div class="list-images mt-3" id="listImages">
                            @foreach ($comment->media as $media)
                                <div class="image-item position-relative d-inline-block">
                                    <img src="{{asset('storage/'. $media->media_url )}}" alt="Image" class="img-thumbnail"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                        onclick="removeExistingImage({{ $media->id }})" style="cursor: pointer;">×</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="deleted_media" id="deletedMedia" value=""> <!-- Lưu danh sách media bị xóa -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select w-25" required>
                            <option value="">--Status--</option>
                            <option value="visible" {{ $comment->status == 'visible' ? 'selected' : '' }}>Visible</option>
                            <option value="hidden" {{ $comment->status == 'hidden' ? 'selected' : '' }}>Hidden</option>
                            <option value="reported" {{ $comment->status == 'reported' ? 'selected' : '' }}>Reported</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const imageInput = document.getElementById('image');
const listImages = document.getElementById('listImages');
let deletedMedia = []; // Danh sách media bị xóa

// Xử lý khi chọn ảnh mới
imageInput.addEventListener('change', function () {
    Array.from(imageInput.files).forEach((file, index) => {
        const imageUrl = URL.createObjectURL(file);
        const imageWrapper = document.createElement('div');
        imageWrapper.classList.add('image-item', 'd-inline-block', 'position-relative', 'me-2', 'mb-2');

        imageWrapper.innerHTML = `
            <img src="${imageUrl}" alt="Image Preview" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                    onclick="removeSelectedImage(${index})" style="cursor: pointer;">&times;</button>
        `;
        listImages.appendChild(imageWrapper);
    });
});

// Xóa ảnh mới được chọn
function removeSelectedImage(index) {
    const files = Array.from(imageInput.files);
    files.splice(index, 1);

    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    imageInput.files = dataTransfer.files;

    const changeEvent = new Event("change");
    imageInput.dispatchEvent(changeEvent);
}

// Xóa ảnh hiện có
function removeExistingImage(mediaId) {
    deletedMedia.push(mediaId); // Thêm ID vào danh sách media bị xóa
    document.getElementById('deletedMedia').value = deletedMedia.join(',');

    const imageElement = event.target.closest('.image-item');
    imageElement.remove(); // Xóa ảnh khỏi giao diện
}
</script>
@endsection