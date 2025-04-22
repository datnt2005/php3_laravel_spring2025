@extends('layouts.appUser')

@section('content')
<div class="container form pt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="form_login image">
                <img src="../../../assets/images/image-in-form.jpeg" alt="" class="w-100"
                    style="height: 650px; object-fit: cover; object-position: center; " />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-register">
                <h2 class="fs-3 fw-bold text-center ">ĐĂNG KÍ</h2>
                <p class="text-center ">Đăng kí để có những trải nghiệm tốt nhất của cửa hàng chúng tôi</p>

                <form action="{{ route('register.create') }}" method="post" class="mt-4">
                    @csrf
                    <!-- Name -->
                    <div class="mt-3">
                        <label class="form-label m-0 fs-6 mb-1">
                            Tên <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="value-forgot d-block w-100"
                            placeholder="Nhập tên"
                            </div>
                        <!-- Email -->
                        <div class="email-phone mt-3">
                            <label class="form-label m-0 fs-6 mb-1">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="email" class="value-forgot d-block w-100 name"
                                placeholder="Nhập Email"
                                </div>
                            <div class="password mt-3">
                                <label class="form-label m-0 fs-6 mb-1">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" class="value-forgot d-block w-100 name"
                                    placeholder="Nhập mật khẩu"
                                    </div>

                                <button type="submit" class="btn btn-dark rounded-0 text-white fw-bold w-100 mt-4" style="height: 45px;">
                                    ĐĂNG KÍ
                                </button>
                            </div>
                </form>
                <p class="mt-3  text-end">
                    Bạn đã có tài khoản? <a href="/login" class="text-primary text-decoration-none">Đăng
                        nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>
<style>
    .form-register {
        padding: 30px;
    }
</style>
@endsection