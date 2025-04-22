<!doctype html>
<html lang="en">

<head>
<meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.webp  " />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title', 'TDShop - Mua sắm thời trang chính hãng')</title>
    <meta name="description" content="@yield('meta_description', 'TDShop là website bán hàng thời trang uy tín. Mua hàng chính hãng, bảo hành rõ ràng.')">
    <meta name="keywords" content="@yield('meta_keywords', 'tdshop, thời trang, bán hàng, chính hãng, mua online')">
    <meta name="author" content="Nguyễn Tiến Đạt">

    {{-- Mạng xã hội: Facebook Open Graph --}}
    <meta property="og:title" content="@yield('og_title', 'TDShop - Mua sắm thời trang chính hãng')">
    <meta property="og:description" content="@yield('og_description', 'Website bán hàng thời trang uy tín, sản phẩm chính hãng, giao hàng toàn quốc.')">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/banner1.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', 'TDShop - Mua sắm thời trang chính hãng')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Mua sắm thời trang chính hãng, bảo hành rõ ràng.')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('assets/images/banner1.jpg'))">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <link rel="icon" href="<%= BASE_URL %>favicon.ico"> -->
    <title>TDShop</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
    }

    .admin-sidebar {
        width: 200px;
        background: rgb(20, 20, 20);
        color: #cecece;
        padding: 20px;
        position: fixed;
        height: 100%;
        overflow-y: auto;
    }

    .menu {
        list-style: none;
        padding: 0;
    }

    .menu-item {
        margin-bottom: 5px;
    }

    .menu-link {
        display: flex;
        align-items: center;
        padding: 10px;
        color: #cecece;
        text-decoration: none;
        transition: background 0.3s, color 0.3s;
        border-left: 5px solid transparent;
    }

    .menu-link:hover {
        border-left-color: #007cba;
        color: #007cba;
    }

    .submenu {
        list-style: none;
        padding-left: 20px;
        display: none;
    }

    .submenu a {
        color: #d4d4d4;
        text-decoration: none;
        display: block;
        padding: 5px 10px;
    }

    .submenu a:hover {
        color: #007cba;
    }

    .arrow {
        margin-left: auto;
        font-size: 0.8rem;
        transition: transform 0.3s;
    }

    .open .arrow {
        transform: rotate(180deg);
    }
</style>

<body>

    <body>
        <div id="wrapper">
            <!-- Sidebar -->
            <div class="admin-sidebar px-1">
                <ul class="menu">
                    <li class="menu-item">
                        <a href="/admin/dashboard" class="menu-link"><i class="fas fa-tachometer-alt me-2"></i> Trang chủ</a>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'posts')">
                            <i class="fa-solid fa-pen-nib  me-2"></i> Bài viết <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="posts">
                            <li><a href="/admin/posts">Danh sách bài viết</a></li>
                            <li><a href="/admin/add-post">Thêm bài viết</a></li>
                            <li><a href="/admin/categories-posts">Danh mục bài viết</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'products')">
                            <i class="fas fa-boxes me-2"></i> Sản phẩm <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="products">
                            <li class="mt-1"><a href="/admin/products">Danh sách sản phẩm</a></li>
                            <li class="mt-1"><a href="/admin/products/create">Thêm sản phẩm</a></li>
                            <li class="mt-1"><a href="/admin/categories">Danh mục</a></li>
                            <li class="mt-1"><a href="/admin/attributes">Biến thể</a></li>
                            <li class="mt-1"><a href="/admin/brands">Nhãn hàng</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'users')">
                            <i class="fas fa-users me-2"></i> Người dùng <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="users">
                            <li class="mt-1"><a href="/admin/users">Tất cả người dùng</a></li>
                            <li class="mt-1"><a href="/admin/users/create">Thêm người dùng</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'orders')">
                            <i class="fas fa-shopping-cart me-2"></i> Đơn hàng <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="orders">
                            <li class="mt-1"><a href="/admin/orders">Tất cả đơn hàng</a></li>
                            <li class="mt-1"><a href="/admin/orders/add">Chờ xác nhận</a></li>
                            <li class="mt-1"><a href="/admin/orders/pending">Chưa thanh toán</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'coupons')">
                            <i class="fas fa-tags me-2"></i> Mã giảm giá <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="coupons">
                            <li class="mt-1"><a href="/admin/coupons">Tất cả mã giảm giá</a></li>
                            <li class="mt-1"><a href="/admin/coupons/create">Thêm mã giảm giá</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'comments')">
                            <i class="fas fa-comments me-2"></i> Bình luận <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="comments">
                            <li class="mt-1"><a href="/admin/comments">Tất cả bình luận</a></li>
                            <li class="mt-1"><a href="/admin/comments/create">Thêm bình luận</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" onclick="toggleMenu(event, 'banners')">
                            <i class="fas fa-images me-2"></i> Banners <span class="arrow">▼</span>
                        </a>
                        <ul class="submenu" id="banners">
                            <li class="mt-1"><a href="/admin/banners">Tất cả banner</a></li>
                            <li class="mt-1"><a href="/admin/banners/create">Thêm banner</a></li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="/admin/settings" class="menu-link"><i class="fas fa-cogs me-2"></i> Cài đặt</a>
                    </li>
                </ul>
            </div>
            <div id="content">

                <nav class="navbar admin-nav">
                    <div class="search-nav mx-5">
                        <form action>
                            <input type="search" name="search" id="search" placeholder="Search...">
                            <button>
                                <i class="fa-solid fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        @auth
                        <li class="nav-item  no-arrow d-flex align-items-center mr-3">
                            <a class="nav-link" href="#" id="user" aria-haspopup="true" aria-expanded="false">
                                <span
                                    class="mr-2 d-none d-lg-inline text-gray-600 small ">{{ Auth::user()->name }}</span>
                                <i class="fa-regular fa-envelope text-gray-400 fs-5 mr-2 mx-2"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="logout">
                                <a class="nav-link" href="/" data-toggle="modal"
                                    data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw fs-5 mr-2 text-gray-400"></i>
                                </a>
                            </div>
                        </li>
                        @endauth
                    </ul>
                </nav>
                <div class="container mt-4">
                    {{-- Hiển thị thông báo lỗi --}}
                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Đã xảy ra lỗi!</strong> Vui lòng kiểm tra lại thông tin nhập vào.
                        <ul class="mt-2 mb-0 p-2">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- Hiển thị thông báo lỗi cụ thể --}}
                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- Hiển thị thông báo thành công --}}
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                </div>

                @yield('content')
            </div>
        </div>

    </body>

</html>
<script>
    function toggleMenu(event, menuId) {
        event.preventDefault();
        let submenu = document.getElementById(menuId);
        if (submenu.style.display === "block") {
            submenu.style.display = "none";
            event.currentTarget.classList.remove("open");
        } else {
            document.querySelectorAll(".submenu").forEach(el => el.style.display = "none");
            document.querySelectorAll(".menu-link").forEach(el => el.classList.remove("open"));
            submenu.style.display = "block";
            event.currentTarget.classList.add("open");
        }
    }
</script>