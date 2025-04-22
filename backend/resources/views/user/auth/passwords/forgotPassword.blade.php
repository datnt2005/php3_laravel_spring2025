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
                        <h2 class="fs-3 fw-bold text-center">QUÊN MẬT KHẨU</h2>
                        <p class="text-center">
                            Thay đổi mật khẩu và trải nghiệm mọi thứ tốt nhất của cửa hàng chúng tôi
                        </p>

                        <!-- Form quên mật khẩu -->
                        <form method="POST" action="{{ route('forgot-password.send-otp') }}" class="mt-4">
                            @csrf
                            <div class="email-forget mt-4">
                                <label class="form-label m-0 fs-6 mb-1">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email"
                                class="value-forgot d-block w-100 name" placeholder="Nhập Email"  />
                            </div>
                            <button type="submit" class="btn w-100 btn-submit fw-bold mt-4" :disabled="isLoading">
                                <span>ĐẶT LẠI MẬT KHẨU</span>
                            </button>
                            
                        </form>
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
