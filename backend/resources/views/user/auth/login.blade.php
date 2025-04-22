@extends('layouts.appUser')

@section('content')
<div class="container form pt-4 pb-5 mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="form_login image">
                <img src="../../../assets/images/image-in-form.jpeg" alt="" class="w-100"
                    style="height: 650px; object-fit: cover; object-position: center; " />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-login">
                <h2 class="fs-3 fw-bold text-center">ĐĂNG NHẬP</h2>
                <p class="text-center">
                    Đăng nhập để có những trải nghiệm tốt nhất của cửa hàng chúng tôi
                </p>

                <!-- Form đăng nhập -->
                <form action="{{ route('login.create') }}" method="post" class="mt-4">
                    @csrf
                    <div class="email-phone">
                        <label class="form-label m-0 mb-1 fs-6">
                            Email hoặc Tên đăng nhập<span class="text-danger">*</span>
                        </label>
                        <input type="text" name="email"
                            class="value-forgot d-block w-100 name"
                            placeholder="Nhập email hoặc tên đăng nhập" />
                    </div>
                    <div class="password-login mt-4">
                        <label class="form-label m-0 fs-6 mb-1">
                            Mật khẩu <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="password"
                         class="value-forgot d-block w-100 name" placeholder="Nhập mật khẩu" />

                    </div>

                    <div class="forgot-pass text-end mt-3">
                        <a href="/forgot-password" class="text-decoration-none text-primary fs-6">
                            Quên mật khẩu?
                        </a>
                    </div>

                    <button type="submit" class="btn w-100 btn-submit fw-bold mt-4" :disabled="isLoading">
                        <span v-else>ĐĂNG NHẬP</span>
                    </button>

                </form>
                <a href="{{ route('auth.google') }}" class="btn btn-transparent d-block border border-primary rounded-5 w-50  mx-auto mt-3 " >
                    <i class="fab fa-google text-primary"></i> Đăng nhập bằng Google
                </a>
                <a href="/register"
                    class="btn text-decoration-none text-dark rounded-0 btn-transparent d-block text-center add-accoun w-100 fs-6 p-2 mt-5 border border-dark">
                    TẠO TÀI KHOẢN
                </a>
            </div>
        </div>
    </div>
</div>
<style>
    .form-login {
        padding: 30px;
    }

    .input-value {
        margin-bottom: 10px;
    }

    .invalid-feedback {
        display: block;
        font-size: 0.875rem;
        color: #dc3545;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    .forgot-pass {
        font-size: 0.875rem;
    }

    .btn-submit {
        font-size: 1.1rem;
        /* padding: 12px; */
        background-color: #333333;
        color: white !important;
        height: 45px;
        text-align: center;
        transition: background-color 0.3s;
    }

    .btn-submit:hover {
        background-color: #525252;
    }
</style>
@endsection