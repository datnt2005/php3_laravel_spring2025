@extends('layouts.appUser')

@section('content')
<main>
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
                    <h2 class="fs-3 fw-bold text-center">QUÊN MẬT KHẨU</h2>
                    <p class="text-center">
                        Thay đổi mật khẩu và trải nghiệm mọi thứ tốt nhất của cửa hàng chúng tôi
                    </p>

                    <!-- Form quên mật khẩu -->
                    <form class="mt-4" method="post" action = "{{ route('reset-password.update') }}">
                        @csrf
                        <input type="hidden" name="email" id="email" value="{{ $email }}">
                        <div class="otp-reset mt-3">
                            <label class="form-label m-0 fs-6 mb-1">
                                OTP <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="otp"
                                class="value-forgot d-block w-100 name" placeholder="Nhập otp"
                              required />
                        </div>
                        <div class="password-reset mt-3">
                            <label class="form-label m-0 fs-6 mb-1">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password"
                                class="value-forgot d-block w-100 name" placeholder="Nhập mật khẩu"
                               required />
                        </div>
                        <div class="passwordConfirm-reset mt-3 ">
                            <label class="form-label m-0 fs-6 mb-1">
                                Nhập lại mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="passwordConfirm"
                                class="value-forgot d-block w-100 name" placeholder="Nhập lại mật khẩu" required />
                                
                        </div>
                        <button type="submit" class="btn w-100 btn-submit fw-bold mt-4" >
                            <span v-else>ĐẶT LẠI MẬT KHẨU</span>
                        </button>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
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
