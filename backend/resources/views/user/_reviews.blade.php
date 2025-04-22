<div class="ratingProduct mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h3 class="fw-bold">ĐÁNH GIÁ SẢN PHẨM</h3>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="comment p-4">
                    <div class="container">
                        <div class="row align-items-center p-4" style="background-color: rgb(204, 204, 204);">
                            <!-- Tổng số sao -->
                            <div class="col-md-3 text-center">
                                <div class="evaluate fs-1 fw-bold text-danger">
                                    <span class="evaluateStart">{{ number_format($averageRating, 1) }}</span>
                                    <span class="fs-4 text-dark">/ 5</span>
                                    <br>
                                    @php
                                        $fullStars = floor($averageRating);
                                        $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                                        $emptyStars = 5 - $fullStars - $halfStar;
                                    @endphp
                                    @for ($i = 0; $i < $fullStars; $i++)
                                        <i class="fa-solid fa-star text-danger fs-4"></i>
                                    @endfor
                                    @if ($halfStar)
                                        <i class="fa-solid fa-star-half-alt text-danger fs-4"></i>
                                    @endif
                                    @for ($i = 0; $i < $emptyStars; $i++)
                                        <i class="fa-regular fa-star text-danger fs-4"></i>
                                    @endfor
                                    <p class="text-muted mt-2 fs-4">({{ count($comments) }})</p>
                                </div>
                            </div>

                            <!-- Lọc đánh giá -->
                            @php
                                $ratingsCount = array_fill(1, 5, 0);
                                foreach ($comments as $comment) {
                                    $ratingsCount[$comment->rating]++;
                                }
                                $totalComments = count($comments);
                            @endphp
                            <div class="col-md-9">
                                <h5 class="fw-bold text-dark">Lọc đánh giá</h5>
                                <div class="list-evaluate">
                                    <ul class="list-unstyled">
                                        @for ($star = 5; $star >= 1; $star--)
                                            @php
                                                $percentage = $totalComments > 0 ? ($ratingsCount[$star] / $totalComments) * 100 : 0;
                                            @endphp
                                            <li class="d-flex align-items-center justify-content-between mb-2">
                                                <a href="javascript:void(0);" class="text-decoration-none text-dark fw-bold filter-rating" data-rating="{{ $star }}">{{ $star }} Sao</a>
                                                <div class="progress w-50">
                                                    <div class="progress-bar bg-danger" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="text-muted ms-2">({{ $ratingsCount[$star] }})</span>
                                            </li>
                                        @endfor
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thêm bình luận -->
                    @if($canComment)
                        <div class="addComment p-4 rounded mt-3">
                            <div class="col-md-12">
                                <hr>
                                <div class="comment-form container">
                                <form id="comment-form" action="{{ route('comments.userAdd', $product->slug) }}" method="POST" enctype="multipart/form-data">
                                 @csrf
                                        <!-- Phần đánh giá sao -->
                                        <div class="d-flex">
                                            <label for="rating" class="form-label fw-bold">Đánh giá</label>
                                            <div class="evaluate d-flex mx-3">
                                                <input type="hidden" name="rating" id="selected-star">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    <button type="button" class="btn d-flex btn-evaluate {{ $i == 5 ? '' : 'border-right: 1px solid #ccc; border-radius: 0;' }}" data-star="{{ $i }}">
                                                        @for ($j = 0; $j < $i; $j++)
                                                            <i class="fa-solid fa-star"></i>
                                                        @endfor
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>
                                        <div id="variant-error" class="text-danger mt-2"></div>

                                        <!-- Phần thông tin bình luận -->
                                        <label for="content" class="form-label fw-bold ">Bình luận</label>
                                        <textarea class="form-control m-0 w-50" id="content" name="content" placeholder="Nhập bình luận ..." required></textarea><br>

                                        <!-- Phần thêm hình ảnh -->
                                        <label for="images" class="form-label fw-bold">Thêm hình ảnh, video</label><br>
                                        <input type="file" id="images" name="images[]" class="form-control w-50" multiple><br>

                                        <!-- Nút gửi bình luận -->
                                        <button type="submit" name="submit_comment" class="btn btn-dark rounded-0">Gửi bình luận</button>
                                    </form>
                                    <hr>

                                    @if ($errors->any())
                                        <div class="alert alert-danger mt-2" style="width: 50%">
                                            @foreach ($errors->all() as $error)
                                                <p>{{ $error }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Danh sách đánh giá -->
                    <div class="container mt-4">
                        <h5 class="fw-bold">Đánh giá từ khách hàng</h5>
                        @if($comments->isEmpty())
                            <p class="text-white text-center">Không có đánh giá nào</p>
                        @else
                            @foreach($comments as $comment)
                                <div class="review mt-3 p-3 rounded shadow-sm " >
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('storage/' . $comment->user->avatar) }}" class="rounded-circle me-3" width="30" height="30" alt="User">
                                        <div>
                                            <h6 class="mb-0">
                                                {{ $comment->user->name }}
                                                <span class="mx-2 fw-normal" style="color: rgb(189, 189, 189);">- {{ $comment->created_at->format('Y-m-d H:i:s') }} -</span>
                                            </h6>
                                            <div class="text-danger">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fa-solid fa-star{{ $i <= $comment->rating ? '' : '-o' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-1">"{{ $comment->content }}"</p>
                                    @if($comment->media->isNotEmpty())
                                        @foreach($comment->media as $media)
                                            @if(str_contains($media->media_type, 'image'))
                                                <img src="{{ asset('storage/' . $media->media_url) }}" alt="Image" style="width: 100px; height: 100px; margin-right: 5px;">
                                            @elseif(str_contains($media->media_type, 'video'))
                                                <video style="width: 100px; height: 70px; margin-right: 5px;" controls>
                                                    <source src="{{ asset('storage/' . $media->media_url) }}" type="{{ $media->media_type }}">
                                                    Your browser does not support the video tag.
                                                </video>
                                            @endif
                                        @endforeach
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div>
                                            <button class="btn btn-sm btn-transparent btn-like p-0 mb-1" data-comment-id="{{ $comment->id }}">
                                                <span class="like-count">{{ $comment->totalLikes }}</span>
                                                <i class="fa-{{ $comment->likedByUser ? 'solid' : 'regular' }} fa-thumbs-up"></i> Like
                                            </button>
                                        </div>
                                        <div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-transparent " type="button" id="dropdownMenuButton{{ $comment->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $comment->id }}">
                                                    @if($comment->user_id !== auth()->id())
                                                        <li><a class="dropdown-item" href="#">Report</a></li>
                                                        <li><a class="dropdown-item" href="#">Share</a></li>
                                                    @else
                                                        <li>
                                                            <button class="btn btn-edit w-100 text-start dropdown-item"
                                                                    data-comment-id="{{ $comment->id }}"
                                                                    data-content="{{ htmlspecialchars($comment->content) }}"
                                                                    data-rating="{{ $comment->rating }}"
                                                                    data-idProduct="{{ $comment->product_id }}">
                                                                Cập nhật
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteComment(this)" data-id="{{ $comment->id }}" data-idProduct="{{ $comment->product_id }}">Delete</a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Updating Comment -->
<div id="updateCommentModal" class="modal">
    <div class="modal-content " >
        <span class="close ms-4 mb-2">×</span>
        <h3 class="fw-bold text-dark text-center">Cập nhật bình luận</h3><br>
        <form id="update-comment-form" action="{{ route('comments.userUpdate', ':commentId') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="commentId" id="commentId">
            <input type="hidden" name="idProduct" id="idProduct">
            <!-- Phần đánh giá sao -->
            <div class="d-flex">
                <label for="rating" class="form-label fw-bold text-dark">Đánh giá</label>
                <div class="evaluate d-flex mx-3">
                    <input type="hidden" name="rating" id="update-selected-star">
                    @for ($i = 5; $i >= 1; $i--)
                        <button type="button" class="btn d-flex btn-evaluate {{ $i == 5 ? '' : 'border-right: 1px solid #ccc; border-radius: 0;' }}" data-star="{{ $i }}">
                            @for ($j = 0; $j < $i; $j++)
                                <i class="fa-solid fa-star"></i>
                            @endfor
                        </button>
                    @endfor
                </div>
            </div>
            <span id="variant-error" class="text-danger"></span><br>
            <label for="update-content" class="form-label fw-bold text-dark">Bình luận</label><br>
            <textarea class="form-control" id="update-content" name="content" placeholder="Nhập bình luận ..." required></textarea><br>
            <label for="update-images" class="form-label fw-bold text-dark">Hình ảnh</label><br>
            <input type="file" id="update-images" name="images[]" class="form-control w-50" multiple><br>
            <button class="btn btn-dark rounded-0 btn-update" type="submit">Cập nhật</button>
        </form>
    </div>
</div>
<div id="alerts-container"></div>

<style>
      .close {
        float: right;
        font-size: 24px;
        cursor: pointer;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 100%;
        max-width: 700px;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-evaluate.active, .btn-evaluate:hover {
        background-color:transparent;
        color:rgb(221, 49, 43);
        border: none;
    }
    .alert { position: fixed; top: 115px; left: 45%; z-index: 1000; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); }
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<script>
    function showAlert(message, type = "danger") {
    const alertContainer = document.getElementById("alerts-container");
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertContainer.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}
    // Filter Reviews
    document.addEventListener('DOMContentLoaded', function() {
        const filterRatings = document.querySelectorAll('.filter-rating');
        const reviews = document.querySelectorAll('.review');

        filterRatings.forEach(filter => {
            filter.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));

                reviews.forEach(review => {
                    const reviewRating = review.querySelectorAll('.text-danger i.fa-solid.fa-star').length;
                    if (rating === reviewRating || isNaN(rating)) {
                        review.style.display = 'block';
                    } else {
                        review.style.display = 'none';
                    }
                });
            });
        });
    });

    // Delete Comment
    function deleteComment(element) {
        if (confirm("Bạn có chắc chắn muốn xóa?")) {
            const id = element.getAttribute('data-id');
            fetch(`/comments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    element.closest('.review').remove();
                    showAlert('Bình luận đã được xóa!', 'success');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }

    // Add Comment
    document.addEventListener('DOMContentLoaded', () => {
        const buttonsAddComment = document.querySelectorAll('#comment-form .btn-evaluate');
        const formComment = document.getElementById('comment-form');

        buttonsAddComment.forEach(button => {
            button.addEventListener('click', () => {
                const evaluate = button.getAttribute('data-star');
                document.getElementById('selected-star').value = evaluate;

                buttonsAddComment.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                button.focus();
            });
        });

        formComment.addEventListener('submit', function(event) {
            const evaluate = document.getElementById('selected-star').value;
            if (!evaluate) {
                event.preventDefault();
                document.getElementById('variant-error').textContent = 'Vui lòng chọn giá trị đánh giá sao.';
            }
        });
    });

    // Update Comment
    document.addEventListener("DOMContentLoaded", function() {
        const updateButtons = document.querySelectorAll(".btn-edit");
        const updateModal = document.getElementById("updateCommentModal");
        const commentIdInput = document.getElementById("commentId");
        const productIdInput = document.getElementById("idProduct");
        const contentInput = document.getElementById("update-content");
        const ratingInput = document.getElementById("update-selected-star");
        const buttonsEvaluate = document.querySelectorAll("#update-comment-form .btn-evaluate");
        const formComment = document.getElementById("update-comment-form");
        const errorRating = document.getElementById("variant-error");
        const closeModalButton = document.querySelector(".close");

        updateButtons.forEach(button => {
            button.addEventListener("click", function() {
                const productId = this.getAttribute("data-idProduct");
                const commentId = this.getAttribute("data-comment-id");
                const content = this.getAttribute("data-content");
                const rating = this.getAttribute("data-rating") || "0";

                productIdInput.value = productId;
                commentIdInput.value = commentId;
                contentInput.value = content;
                ratingInput.value = rating;

                buttonsEvaluate.forEach(btn => {
                    btn.classList.remove("active");
                    if (btn.getAttribute("data-star") === rating) {
                        btn.classList.add("active");
                    }
                });

                updateModal.style.display = "block";

                // Update form action with correct comment ID
                formComment.action = formComment.action.replace(':commentId', commentId);
            });
        });

        closeModalButton.addEventListener("click", function() {
            updateModal.style.display = "none";
        });

        buttonsEvaluate.forEach(button => {
            button.addEventListener('click', () => {
                const evaluate = button.getAttribute('data-star');
                ratingInput.value = evaluate;

                buttonsEvaluate.forEach(btn => btn.classList.remove("active"));
                button.classList.add("active");
                button.focus();
            });
        });

        formComment.addEventListener("submit", function(event) {
            const evaluate = ratingInput.value;
            if (!evaluate) {
                event.preventDefault();
                errorRating.textContent = "Vui lòng chọn số sao.";
                errorRating.style.color = "red";
            } else {
                errorRating.textContent = "";
            }
        });
    });

    // Like Comment
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".btn-like").forEach(button => {
            button.addEventListener("click", function() {
                const commentId = this.getAttribute("data-comment-id");
                const likeCountElement = this.querySelector(".like-count");
                const iconElement = this.querySelector("i");

                fetch(`/comments/${commentId}/toggle-like`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ commentId: commentId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        likeCountElement.textContent = data.totalLikes;
                        if (data.liked) {
                            iconElement.classList.remove("fa-regular");
                            iconElement.classList.add("fa-solid");
                        } else {
                            iconElement.classList.remove("fa-solid");
                            iconElement.classList.add("fa-regular");
                        }
                    } else {
                        showAlert(data.message, "danger");
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });
    });
</script>