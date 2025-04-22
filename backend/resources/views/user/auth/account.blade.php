@extends('layouts.appUser')
@section('content')
<main>
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mt-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="nav-link">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tài khoản</li>
            </ol>
        </nav>
    </div>

    <div class="container form">
        <div class="row">
            <div class="col-md-3 mt-1">
                <div class="aside-account mt-4">
                    <h2 class="fw-bold fs-3">TÀI KHOẢN</h2>
                    <ul class="mx-3 aside-list">
                        <li class="nav-link"><a href="#" class="text-decoration-none aside-account-list focus-in">Thông tin tài khoản</a></li>
                        <li class="nav-link"><a href="#" class="text-decoration-none aside-account-list">Địa chỉ</a></li>
                        <li class="nav-link"><a href="#" class="text-decoration-none aside-account-list">Quản lí đơn hàng</a></li>
                        <li class="nav-link"><a href="#" class="text-decoration-none aside-account-list">Danh sách yêu thích</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-login">
                    <form action="{{ route('update-account', Auth::user()->id) }}" method="POST" enctype="multipart/form-data" class="mt-2 mb-3">
                        @csrf
                        <div class="avatar-value d-flex justify-content-center align-items-center">
                            <input type="file" name="avatar" id="avatar" class="value-forgot d-block w-25 h-25 mx-3">
                            <img src="{{ asset('storage/'. Auth::user()->avatar) }}" alt="Avatar" class="avatar rounded-circle" height="100" width="100">
                        </div>
                        <div class="name-value mt-2">
                            <label class="form-label m-0">Tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="value-forgot d-block w-100" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="email-value mt-4">
                            <label class="form-label m-0">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="value-forgot d-block w-100" value="{{ Auth::user()->email }}" required>
                        </div>
                        <div class="number-phone mt-4">
                            <label class="form-label m-0 d-block">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="value-forgot d-block w-100" value="{{ Auth::user()->phone }}" placeholder="Thêm số điện thoại" required>
                        </div>
                        <div class="password-value password-login mt-4">
                            <div class="d-flex justify-content-end">
                            <span class="change-password text-warning" onclick="togglePasswordForm(event)" style="cursor: pointer;">Đặt lại mật khẩu?</span>                            </div>
                        </div>
                        <button class="btn btn-dark w-100 btn-submit mt-4">CẬP NHẬT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Form đổi mật khẩu (Ẩn ban đầu) -->
    <div id="passwordModal" class="password-modal d-none w-25" aria-hidden="true">
    <div class="password-modal-content">
        <span class="close ms-4 mb-2" onclick="togglePasswordForm(event)">×</span>
        <h3 class="text-center">Đổi mật khẩu</h3>
        <div class="password-modal-body">
            <form action="{{ route('change-password', Auth::user()->id ) }}" method="POST">
                @csrf
                <div class="old-password password-login mt-4">
                    <label class="form-label m-0">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="oldPassword" id="oldPassword" class="value-forgot d-block w-100 password" autocomplete="new-password" required>
                </div>
                <div class="new-password password-login mt-4">
                    <label class="form-label m-0">Mật khẩu mới<span class="text-danger">*</span></label>
                    <input type="password" name="newPassword" id="newPassword" class="value-forgot d-block w-100 password" autocomplete="new-password" required>
                </div>
                <div class="new-passwordConfirm password-login mt-4">
                    <label class="form-label m-0">Nhập lại mật khẩu mới<span class="text-danger">*</span></label>
                    <input type="password" name="newPasswordConfirm" id="newPasswordConfirm" class="value-forgot d-block w-100 password" autocomplete="new-password" required>
                </div>
                @if(session('error'))
                <span class="text-danger fs-6 mt-2">{{ session('error') }}</span>
                @endif
                <button type="submit" class="btn btn-dark w-100 rounded-0 mt-3">XÁC NHẬN</button>
            </form>
        </div>
    </div>
</div>
<div id="overlay" class="overlay d-none"></div>
</main>

<style>
    input[name="email"] {
        display: none;
    }

    .password-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        z-index: 1002;
        width: 300px;
    }

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1001;
    }

    .d-none {
        display: none ;
    }

    .close {
        float: right;
        font-size: 24px;
        cursor: pointer;
    }

</style>

<script>
    function togglePasswordForm(event) {
    event.preventDefault(); // Ngăn chặn hành vi mặc định của thẻ <a>
    const modal = document.getElementById('passwordModal');
    const overlay = document.getElementById('overlay');
    const isHidden = modal.classList.contains('d-none');
    modal.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
    modal.classList.toggle('d-none');
    overlay.classList.toggle('d-none');
}
</script>
@endsection