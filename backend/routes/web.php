<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\BannerController;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Order;

Route::get('/devfolio', function () {
    $info= array(
        'name' => 'NGUYEN TIEN DAT',
        'major' => 'Backend Dev',
        'email' => 'ntdad2005@gmail.com',
        'phone' => '0847701203',
        'address' => 'Buon Ma Thuot, Dak Lak',
        'about' => 'Tôi là Nguyễn Tiến Đạt, một lập trình viên web với niềm đam mê xây dựng và phát triển các ứng dụng web tối ưu,
        tôi đang tập trung vào việc cải thiện khả năng giao tiếp tiếng Anh để mở rộng cơ hội làm việc và hợp tác quốc tế.',
        'skill' => 'PHP, Laravel, HTML, CSS, JavaScript, jQuery, Bootstrap, MySQL, Git, GitHub, Jira, Trello',
        'design' => 'Thiết kế website chuyên nghiệp, tối ưu trải nghiệm người dùng, giúp doanh nghiệp xây dựng hình ảnh thương hiệu trực tuyến.',
        'development' => 'Phát triển website với công nghệ mới nhất, đảm bảo hiệu suất cao, bảo mật tốt và dễ dàng mở rộng.',
        'photography' => 'Dịch vụ chụp ảnh chuyên nghiệp cho doanh nghiệp, sự kiện, sản phẩm, giúp tạo dấu ấn riêng trên thị trường.',
        'responsive' => 'Thiết kế website tương thích với mọi thiết bị, đảm bảo trải nghiệm người dùng tốt nhất trên máy tính, điện thoại và máy tính bảng.',
        'graphic' => 'Thiết kế đồ họa sáng tạo, từ logo, banner, poster đến bộ nhận diện thương hiệu, giúp doanh nghiệp nổi bật hơn.',
        'marketing' => 'Chiến lược marketing hiệu quả, từ SEO, quảng cáo Google, Facebook đến Email Marketing, giúp tăng trưởng doanh thu bền vững.',
        'learning' => 'Lập trình Web - Cao đẳng FPT Polytechnic Tây Nguyên',
    );
    return view('devfolio', compact('info'));
});
Route::get('/users/{id}', function (string $id) {
    $users = array(
        '1' => array(
            'name' => 'NGUYEN TIEN DAT',
            'major' => 'Backend Dev',
            'email' => 'ntdad2005@gmail.com',
            'phone' => '0847701203'
        ),
        '2' => array(
            'name' => 'NGUYEN VAN TIEN',
            'major' => 'Frontend Dev',
            'email' => 'tien123@gmail.com',
            'phone' => '0987654321'
        )
    );
    if(!empty($users[$id])){
        $user = $users[$id];
        return view('demo', compact('user'));
    }else{
        // abort(404);
        echo "do qua";
    }
});
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/',[HomeController::class, 'index'])->name('home');





// Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/forgot-password', function () {
    return view('user/auth/passwords/forgotPassword');
});
Route::get('/reset-password', function () {
    return view('user/auth/passwords/resetPassword');
});

//admin
Route::middleware('role:admin')->group(function () {
    Route::get('/admin', function () {
        return view('admin/index');
    })->name('admin.index');

    Route::get('/admin/dashboard' , [OrderController::class, 'revenue'])->name('admin.dashboard');
    //category
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/admin/categories/create', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/admin/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit'); 
    Route::post('/admin/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'delete'])->name('categories.delete');
    Route::put('/admin/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    //brand
    Route::get('/admin/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/admin/brands/create', [BrandController::class, 'store'])->name('brands.store');
    Route::get('/admin/brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit'); 
    Route::post('/admin/brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::delete('/admin/brands/{id}', [BrandController::class, 'delete'])->name('brands.delete');
    Route::put('/admin/brands/{id}', [BrandController::class, 'update'])->name('brands.update');

    //attribute
    Route::get('/admin/attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/admin/attributes/create', [AttributeController::class, 'store'])->name('attributes.store');
    Route::post('/admin/attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
    Route::get('/admin/attributes/{id}/edit', [AttributeController::class, 'edit'])->name('attributes.edit');
    Route::put('/admin/attributes/{id}', [AttributeController::class, 'update'])->name('attributes.update');
    Route::delete('/admin/attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.delete');

    //product
    Route::get('/admin/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/admin/products/create', [ProductController::class, 'store'])->name('products.store');
    Route::get('/admin/products/createFile', [ProductController::class, 'storeFile'])->name('products.storeFile');
    Route::get('/admin/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit'); 
    Route::post('/admin/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/admin/products/createFile', [ProductController::class, 'import'])->name('products.createFile');
    Route::delete('/admin/products/{id}', [ProductController::class, 'delete'])->name('products.delete');
    Route::put('/admin/products/{id}', [ProductController::class, 'update'])->name('products.update');
    //user
    Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/create', [UserController::class, 'store'])->name('users.store');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit'); 
    Route::post('/admin/users/create', [UserController::class, 'create'])->name('users.create');
    Route::delete('/admin/users/{id}', [UserController::class, 'delete'])->name('users.delete');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('users.update');

    //coupon
    Route::get('/admin/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/admin/coupons/create', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('/admin/coupons/{id}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
    Route::post('/admin/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
    Route::delete('/admin/coupons/{id}', [CouponController::class, 'delete'])->name('coupons.delete');
    Route::put('/admin/coupons/{id}', [CouponController::class, 'update'])->name('coupons.update');

    //ỏder
    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::post('/admin/orders/change-status', [OrderController::class, 'changeStatus'])->name('admin.orders.changeStatus');
    Route::get('/admin/orders/detail/{id}', [OrderController::class, 'getDetail'])->name('orders.detail');
    Route::post('/admin/orders/update-status', [OrderController::class, 'updateOrderStatusToGHN'])->name('admin.orders.update-status');

    //comment
    Route::get('/admin/comments', [CommentController::class,'index'])->name('comment.index');
    Route::get('/admin/comments/create', [CommentController::class,'store'])->name('comment.store');
    Route::post('/admin/comments/create', [CommentController::class,'create'])->name('comment.create');
    Route::delete('/admin/comments/{id}', [CommentController::class, 'remove']) ->name('comment.delete');
    Route::get('/admin/comments/{id}/edit', [CommentController::class, 'edit'])->name('comment.edit');
    Route::put('/admin/comments/{id}/edit', [CommentController::class, 'update'])->name('comment.update');
     
    //coupon
     Route::get('/admin/banners', [BannerController::class, 'index'])->name('banners.index');
     Route::get('/admin/banners/create', [BannerController::class, 'store'])->name('banners.store');
     Route::get('/admin/banners/{id}/edit', [BannerController::class, 'edit'])->name('banners.edit');
     Route::post('/admin/banners/create', [BannerController::class, 'create'])->name('banners.create');
     Route::delete('/admin/banners/{id}', [BannerController::class, 'delete'])->name('banners.delete');
     Route::put('/admin/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
});

//user
Route::middleware('role:user' ,  )->group(function () {
    Route::get('/account', [UserController::class, 'account'])->name('account');
    Route::post('/change-password/{id}', action: [UserController::class, 'changePassword'])->name('change-password');
    Route::post('/update-account/{id}', [UserController::class, 'updateAccount'])->name('update-account');

    // cart
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/delete/{id}', [CartController::class, 'delete'])->name('cart.delete');
    Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');

    //order
    Route::get('/checkout', [OrderController::class, 'showCheckout'])->name('checkout.index');
    Route::get('/select-address', [OrderController::class, 'selectAddress'])->name('select-address');
    Route::post('/checkout/apply-coupon', [OrderController::class, 'applyCoupon'])->name('checkout.apply-coupon');
    Route::post('/checkout/create', [OrderController::class, 'createOrder'])->name('checkout.create');

    //address
    Route::get('/address', [AddressController::class, 'index'])->name('address.index');
    Route::post('/address/create', [AddressController::class, 'create'])->name('address.create');
    Route::delete('/address/delete/{id}', [AddressController::class, 'destroy'])->name('address.delete');
    Route::post('/address/update', [AddressController::class, 'update'])->name('address.update');

    //vnpay
    Route::post('/vnpay/create', [OrderController::class, 'createPaymentVnpay'])->name('vnpay.create');
    Route::get('/vnpay/return', [OrderController::class, 'return'])->name('vnpay.return');
    Route::post('/vnpay/notify', [OrderController::class, 'notify'])->name('vnpay.notify');

    //order
    Route::get('/orders', [OrderController::class, 'showOrderBuyed'])->name('orders.index');
    Route::get('/orders/detail/{id}', [OrderController::class, 'getDetail'])->name('orders.detail');
    Route::post('/orders/reorder/{id}', [OrderController::class, 'reorder'])->name('orders.reorder');
    Route::post('/orders/cancel/{id}', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/remove/{id}', [OrderController::class, 'removeOrder'])->name('orders.remove');

    Route::get('/favorite', [FavoriteController::class, 'index'])->name('favorite.index');
    Route::post('/favorite/{product_id}', [FavoriteController::class, 'toggleFavorite'])->name('favorites.toggle');

});

//auth
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/login',[UserController::class, 'viewLogin'])->name('login');
Route::get('/register', [UserController::class, 'viewRegister'])->name('register');
Route::post('/register/create', [RegisterController::class, 'register'])->name('register.create');
Route::post('/login/create', [LoginController::class, 'login'])->name('login.create');
Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password.index');
Route::post('/forgot-password/send-otp', action: [ForgotPasswordController::class, 'sendOtp'])->name('forgot-password.send-otp'); // Gửi OTP
Route::get('/reset-password', [ResetPasswordController::class, 'index'])->name('reset-password.index'); // Gửi OTP
Route::post('/reset-password/update', [ResetPasswordController::class, 'resetPassword'])->name('reset-password.update'); // Gửi OTP
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
//shop
Route::get('/shop', [ProductController::class, 'shop'])->name('shop.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
//cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::get('/ghn/provinces', [LocationController::class, 'getProvinces']);
Route::get('/ghn/districts/{province_id}', [LocationController::class, 'getDistricts']);
Route::get('/ghn/wards/{district_id}', [LocationController::class, 'getWards']);
Route::post('/ghn/shipping-fee', [LocationController::class, 'calculateShippingFee']);
Route::get('/address/details/{id}', [AddressController::class, 'getDetails']);
Route::get('/update-ghn-status/{tracking_code}', [OrderController::class, 'updateStatus']);

//bo trong auth user
Route::post('/product/{slug}/comment', [CommentController::class, 'userCreateComment'])->name('comments.userAdd');
Route::put('/comments/{id}', [CommentController::class, 'userUpdateComment'])->name('comments.userUpdate');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
//end
Route::post('/comments/{commentId}/toggle-like', [CommentController::class, 'toggleLike'])->name('comments.toggleLike');

Route::get('/momo/payment', [OrderController::class, 'createPaymentMomo'])->name('momo.payment');
Route::get('/momo/return', [OrderController::class, 'returnMomo'])->name('momo.return');

//AI
Route::post('/ai/chat', [AIChatController::class, 'chat']);
Route::post('/ai/add-to-cart', [AIChatController::class, 'addToCart']);

