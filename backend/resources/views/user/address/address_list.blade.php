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
                <h2 class="text-center text-white">THÔNG TIN ĐỊA CHỈ</h2>
                    
                    <div class="form-login w-50 m-auto">
                        <form action="" method="POST">
                            <div class="add_address text-center w-5 rounded btn btn-info">
                                <a href="addAddress.php" class="text-dark fw-bold text-decoration-none">Thêm địa chỉ</a>
                            </div>
                            <div class="row mt-4">
                                @foreach($addresses as $address)
                                <div class="col-md-9">
                                    <div class="address lh-1">
                                        <div class="d-flex">
                                           

                                            @if ($address['isDefault'] == 1)
                                                <input type="radio" name="address" id="" value="{{ $address['id'] }}" required checked>
                                            @else
                                                <input type="radio" name="address" id="" value="{{ $address['id'] }}" required>
                                            @endif
                                            <label for="" class="mt-2 mx-3">{{ $address->name }} | {{ $address->phone }}</label>
                                        </div>
                                        <div class="infomation mx-4">
                                            <p>{{ $address['ward'] }}</p>
                                            <p>{{ $address['street'] }}</p>
                                            <p>{{ $address['address'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="edit-address">
                                        <a href="updateAddress.php?idAddress=<?php echo $address['idDetailAddress']; ?>" class='text-decoration-none text-center'>
                                            <p class="text-warning fw-bold">Sửa đổi</p>
                                        </a>
                                    </div>
                                    <?php 
                                    if(!empty($defaultAddress) && $defaultAddress[0]['idDetailAddress'] == $address['idDetailAddress']) {
                                        echo '
                                        <div class="defaul-address border border-danger-subtle rounded">
                                            <p class="text-danger text-center mt-2">Mặc định</p>
                                        </div>';
                                    }
                                    ?>
                                </div>
                                <hr>
                                <?php endforeach; ?>
                                <button type="submit" name="submit" class="btn btn-primary">CHỌN</button>
                            </div>
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
        display: none !important;
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