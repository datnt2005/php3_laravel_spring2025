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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TDShop</title>
</head>

<body>

    <body>
        <div id="loader"
            style="position: fixed; z-index: 9999; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center;">
            <div class="dot-loader">
                <span></span><span></span><span></span>
            </div>
        </div>

        <style>
        .dot-loader {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .dot-loader span {
            width: 10px;
            height: 10px;
            background-color: rgb(58, 58, 58);
            border-radius: 50%;
            display: inline-block;
            animation: bounce 0.6s infinite ease-in-out;
        }

        .dot-loader span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot-loader span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-15px);
            }
        }
        </style>
        <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            if (loader) {
                loader.style.display = 'none';
            }
        });
        </script>
        <header>
            <div class="header container d-flex justify-content-between align-items-center py-2">
                <div class="logo-header">
                    <a href="/">
                        <img src="../assets/images/logo.webp" width="60" alt>
                    </a>
                </div>

                {{-- Nút menu hamburger --}}
                <button class="btn d-lg-none" id="toggleMenu">
                    <i class="fa-solid fa-bars fa-xl"></i>
                </button>

                {{-- Nav menu --}}
                <nav id="mainNav" class="d-none d-lg-block">
                    <ul class="d-lg-flex flex-column flex-lg-row text-center">
                        <li class="nav-link mx-3"><a href="/shop" class="nav-items">HÀNG MỚI</a></li>
                        <li class="nav-link mx-3"><a href="/shop?category=ao" class="nav-items">ÁO</a></li>
                        <li class="nav-link mx-3"><a href="/shop?category=quan" class="nav-items">QUẦN</a></li>
                        <li class="nav-link mx-3"><a href="/shop?category=phu-kien" class="nav-items">PHỤ KIỆN</a></li>
                        <li class="nav-link mx-3"><a href="/shop" class="nav-items">GIÁ TỐT</a></li>
                        <li class="nav-link mx-3"><a href="/shop" class="nav-items">CỬA HÀNG</a></li>
                    </ul>
                </nav>

                <div class="header-search d-none d-lg-block">
                    <form action="{{ route('shop.index') }}" method="GET">
                        <input type="search" class="input-search" name="name" id="name"
                            placeholder="Bạn tìm sản phẩm gì...">
                        <button type="submit">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </form>
                </div>

                <div class="cart-user">
                    <ul class="d-flex justify-content-between align-items-center">
                        <li class="nav-link mx-2">
                            <a href="/favorite">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-heart">
                                    <path
                                        d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                </svg>
                            </a>
                        </li>
                        <li class="nav-link mx-2 position-relative">
                            <a href="/cart">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-shopping-bag">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                                <span id="cart-count" class="badge-cart">{{ session('cart', 0) }}</span>
                            </a>
                        </li>
                        <li class="nav-link mx-2 dropdown">
                            @auth
                            <a href="#" class="" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="User Avatar"
                                    class="rounded-circle" width="35" height="35">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/account">Hồ sơ</a></li>
                                <li><a class="dropdown-item" href="/orders">Đơn mua</a></li>
                                @if (Auth::user()->role == 'admin')
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Quản lý</a></li>
                                @endif
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"
                                            onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?')">Đăng
                                            xuất</button>
                                    </form>
                                </li>
                            </ul>

                            @else
                            <a href="/login">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-user-round">
                                    <circle cx="12" cy="8" r="5" />
                                    <path d="M20 21a8 8 0 0 0-16 0" />
                                </svg>
                            </a>
                            @endauth
                        </li>
                    </ul>
                </div>
            </div>

            <div class="container-fluid p-0">
                <div class="d-flex justify-content-center align-items-center header-outstanding">
                    <p class="link-cate m-1 fw-bold">BE CONFIDENT - <a href="#" class="text-dark fw-bold ">OUT NOW</a>
                    </p>
                </div>
            </div>
        </header>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleMenu = document.getElementById('toggleMenu');
            const mainNav = document.getElementById('mainNav');

            if (toggleMenu && mainNav) {
                toggleMenu.addEventListener('click', () => {
                    mainNav.classList.toggle('d-none');
                });
            }
        });
        </script>
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

        <footer class="mt-3 pb-4 bg-light">
            <hr class="m-0">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="address">
                            <div class="logo-footer">
                                <a href="/" class="mt-4">
                                    <img src="../assets/images/logo.webp" alt class="d-block mx-auto mt-4" width="80">
                                </a>
                            </div>
                            <div class="info-footer mt-4">
                                <p class="m-0 fw-bold">CÔNG TY CỔ PHẦN THỜI
                                    TRANG TDSHOP</p>
                                <p><span class="fw-bold">Hotline</span>: 1900
                                    1000</p>
                            </div>
                            <div class="address-footer">
                                <p class="m-0"><span class="fw-bold">Địa chỉ:</span>
                                    Tượng đài chiến thắng Buôn Ma Thuột, TP. Buôn Ma Thuột, tỉnh Đắk Lắk.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4">
                        <div class="footer-items">
                            <h5 class="fs-6 fw-bold">GIỚI THIỆU VỀ TDSHOP</h5>
                            <ul>
                                <li class="nav-link">
                                    <a href="#">Giới thiệu</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">BLog</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Hệ thống cửa hàng</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Liên hệ Princes</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Chính sách bảo mật</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4">
                        <div class="footer-items">
                            <h5 class="fs-6 fw-bold">HỖ TRỢ KHÁCH HÀNG</h5>
                            <ul>
                                <li class="nav-link">
                                    <a href="#">Hỏi đáp</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Chính sách vận chuyển</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Hướng dẫn chọn kích cỡ</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Hướng dẫn thanh toán</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Quy định đổi hàng</a>
                                </li>
                                <li class="nav-link">
                                    <a href="#">Hướng dẫn mua hàng</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4">
                        <div class="footer-items">
                            <h5 class="fs-6 fw-bold">KẾT NỐI</h5>
                            <div class="face-ins d-flex">
                                <a href="#" class="nav-link ml-2">
                                    <i class="fa-brands fa-facebook fs-4"></i>
                                </a>
                                <a href="#" class="nav-link ms-2">
                                    <i class="fa-brands fa-instagram fs-4"></i>
                                </a>
                                <a href="#" class="nav-link ms-2">
                                    <i class="fa-brands fa-youtube fs-4"></i>
                                </a>
                            </div>
                            <h5 class="fs-6 fw-bold my-3">PHƯƠNG THỨC THANH
                                TOÁN</h5>
                            <div class="checkout">
                                <img src="https://owen.cdn.vccloud.vn/static/version1718818632/frontend/Owen/owen2021/vi_VN/images/pay.png"
                                    alt>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <button onclick="toggleChatbox()" class="chat-button">
            <img src="{{ asset('assets/images/logo.webp') }}" width="35" alt="">
        </button>
        <div id="chatbox" class="chatbox ">
            <div class="chatbox-header">
                <p class="fw-bold fs-5 d-flex mx-auto">TRỢ LÝ NÓNG BỎNG🔥</p>
                <button class="close mx-2" onclick="toggleChatbox()">&times;</button>
            </div>
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4"></div>
            <div class="p-2 border-t d-flex">
                <input id="chat-input" type="text" class="w-100 p-2 border rounded me-2" placeholder="Nhập tin nhắn..."
                    onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()" class=" w-25 btn btn-primary text-white p-2 rounded">Gửi</button>
            </div>
        </div>
        <div id="variant-modal" class="variant-modal">
            <div class="variant-modal-content">
                <h3 class="text-lg font-bold mb-4 text-center">Chọn biến thể sản phẩm</h3>
                <div id="variant-options"></div>
                <div class="mt-4 d-flex mx-auto gap-2">
                    <button onclick="closeVariantModal()"
                        class="px-4 py-2 btn btn-tranfarent border-dark rounded-0">Hủy</button>
                    <button onclick="confirmBuy()" class="px-4 py-2 btn btn-dark text-white rounded-0">Xác nhận</button>
                </div>
            </div>
        </div>

    </body>

</html>
<style>

</style>
<script>
let isChatOpen = false;
let selectedProduct = null;

function toggleChatbox() {
    const chatbox = document.getElementById('chatbox');
    isChatOpen = !isChatOpen;
    chatbox.style.display = isChatOpen ? 'flex' : 'none';
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;

    const messagesDiv = document.getElementById('chat-messages');
    messagesDiv.innerHTML += `<div class="message user-message"><div class="bubble">${escapeHtml(message)}</div></div>`;
    input.value = '';
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    fetch('/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                message
            })
        })
        .then(res => res.json())
        .then(data => {
            const response = data.response;
            if (response.includes('Dựa trên yêu cầu của bạn, đây là các sản phẩm phù hợp:')) {
                const products = parseProductResponse(response);
                products.forEach(product => {
                    messagesDiv.innerHTML += createProductCard(product);
                });
            } else if (response.includes('Chi tiết sản phẩm:')) {
                const product = parseDetailedProductResponse(response);
                messagesDiv.innerHTML += createDetailedProductCard(product);
            } else if (response.includes('Dưới đây là Top 5 sản phẩm bán chạy:')) {
                const products = parseProductTrending(response);
                products.forEach(product => {
                    messagesDiv.innerHTML += createProductTrending(product); // Use createProductTrending
                });
            } else {
                messagesDiv.innerHTML +=
                    `<div class="message bot-message"><div class="bubble">${escapeHtml(response)}</div></div>`;
            }
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        })
        .catch(() => {
            messagesDiv.innerHTML +=
                `<div class="message bot-message"><div class="bubble">Lỗi khi gửi. Vui lòng thử lại.</div></div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function parseProductResponse(response) {
    const products = [];
    const lines = response.split('\n');
    let isProductSection = false;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (line.includes('Dựa trên yêu cầu của bạn, đây là các sản phẩm phù hợp:')) {
            isProductSection = true;
            continue;
        }
        if (isProductSection && line.startsWith('- ')) {
            const productLine = line.substring(2);
            const linkLine = lines[++i].trim();
            const imageLine = lines[++i].trim();

            const match = productLine.match(
                /^(.+?)\s*\((.+?),\s*(.+?),\s*ID biến thể:\s*(\d+),\s*ID sản phẩm:\s*(\d+)\):\s*([\d,]+)\s*VNĐ$/);
            if (match) {
                const [, name, color, size, variantId, productId, price] = match;
                const linkMatch = linkLine.match(/^Link:\s*(.+)$/);
                const imageMatch = imageLine.match(/^Image:\s*(.+)$/);
                const link = linkMatch ? linkMatch[1] : '#';
                const image = imageMatch ? imageMatch[1] : '#';

                if (color === 'undefined' || size === 'undefined') continue;

                products.push({
                    name,
                    color,
                    size,
                    variantId: parseInt(variantId),
                    productId: parseInt(productId),
                    price: parseInt(price.replace(/,/g, '')),
                    link,
                    image,
                    variants: [{
                        variantId: parseInt(variantId),
                        productId: parseInt(productId),
                        color,
                        size,
                        price: parseInt(price.replace(/,/g, ''))
                    }]
                });
            }
        }
    }

    return products;
}

function parseProductTrending(response) {
    const products = [];
    const lines = response.split('\n');
    let currentProduct = {};

    lines.forEach(line => {
        line = line.trim();

        if (line.startsWith("🛍️")) {
            if (Object.keys(currentProduct).length) {
                products.push({
                    ...currentProduct
                });
            }
            currentProduct = {
                name: line.replace("🛍️", "").trim()
            };
        } else if (line.startsWith("- Màu:")) {
            const match = line.match(/- Màu:\s*(\{.*?\}),\s*Kích thước:\s*(\{.*?\})/);
            if (match) {
                try {
                    const colorObj = JSON.parse(match[1]);
                    const sizeObj = JSON.parse(match[2]);
                    currentProduct.color = colorObj.value || 'Không xác định';
                    currentProduct.size = sizeObj.value || 'Không xác định';
                } catch (e) {
                    currentProduct.color = 'Không xác định';
                    currentProduct.size = 'Không xác định';
                }
            } else {
                currentProduct.color = 'Không xác định';
                currentProduct.size = 'Không xác định';
            }
        } else if (line.startsWith("- Giá:")) {
            const priceText = line.replace("- Giá:", "").replace("VNĐ", "").trim();
            currentProduct.price = parseInt(priceText.replace(/,/g, ''));
        } else if (line.startsWith("- 🔗 Link:")) {
            currentProduct.link = line.replace("- 🔗 Link:", "").trim();
        } else if (line.startsWith("- 🖼️ Hình ảnh:")) {
            currentProduct.image = line.replace("- 🖼️ Hình ảnh:", "").trim();
        } else if (line.startsWith("- 🆔 ID Biến thể:")) {
            const match = line.match(/- 🆔 ID Biến thể:\s*(\d+),\s*ID Sản phẩm:\s*(\d+)/);
            if (match) {
                currentProduct.variantId = parseInt(match[1]);
                currentProduct.productId = parseInt(match[2]);

                currentProduct.variants = [{
                    variantId: currentProduct.variantId,
                    productId: currentProduct.productId,
                    color: currentProduct.color,
                    size: currentProduct.size,
                    price: currentProduct.price
                }];
            }
        }
    });

    if (Object.keys(currentProduct).length) {
        products.push({
            ...currentProduct
        });
    }

    console.log('Parsed trending products:', products); // For debugging
    return products;
}

function parseDetailedProductResponse(response) {
    const product = {
        name: '',
        description: '',
        images: [],
        variants: [],
        link: '',
        productId: null
    };
    const lines = response.split('\n');
    let isVariantsSection = false;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();

        if (line.startsWith('Chi tiết sản phẩm:')) {
            product.name = line.replace('Chi tiết sản phẩm:', '').trim();
        } else if (line.startsWith('Mô tả:')) {
            product.description = line.replace('Mô tả:', '').trim();
        } else if (line.startsWith('Hình ảnh:')) {
            product.images = line.replace('Hình ảnh:', '').trim().split(', ');
        } else if (line.startsWith('Link:')) {
            product.link = line.replace('Link:', '').trim();
        } else if (line.startsWith('ID sản phẩm:')) {
            const match = line.match(/ID sản phẩm:\s*(\d+)/);
            if (match) product.productId = parseInt(match[1]);
        } else if (line.startsWith('Các biến thể:')) {
            isVariantsSection = true;
            continue;
        } else if (isVariantsSection && line.startsWith('- Màu:')) {
            const variant = {};

            // Tách chuỗi JSON trong phần Màu và Kích thước
            const colorJsonMatch = line.match(/Màu:\s*(\{.*?\}),\s*Kích thước:\s*(\{.*?\})/);
            if (colorJsonMatch) {
                try {
                    const colorObj = JSON.parse(colorJsonMatch[1]);
                    const sizeObj = JSON.parse(colorJsonMatch[2]);
                    variant.color = colorObj.value || 'Không xác định';
                    variant.size = sizeObj.value || 'Không xác định';
                } catch (e) {
                    variant.color = 'Không xác định';
                    variant.size = 'Không xác định';
                }
            }

            // ID biến thể và ID sản phẩm
            const idMatch = line.match(/ID biến thể:\s*(\d+),\s*ID sản phẩm:\s*(\d+)/);
            if (idMatch) {
                variant.variantId = parseInt(idMatch[1]);
                variant.productId = parseInt(idMatch[2]);
            }

            // Giá và tồn kho
            const priceLine = lines[++i]?.trim();
            const priceMatch = priceLine?.match(/Giá:\s*([\d,]+)\s*VNĐ/);
            if (priceMatch) {
                variant.price = parseInt(priceMatch[1].replace(/,/g, ''));
            }

            const stockLine = lines[++i]?.trim();
            const stockMatch = stockLine?.match(/Tồn kho:\s*(\d+)/);
            if (stockMatch) {
                variant.stock = parseInt(stockMatch[1]);
            }

            product.variants.push(variant);
        }

    }

    return product;
}

function createProductCard(product) {
    return `
                <div class="message bot-message">
                    <div class="product-card flex gap-3 p-2 rounded shadow-sm bg-gray-50" style="max-width: 500px; border: 1px solid #ddd;">
                        <img src="${product.image}" alt="${product.name}" width="100" height="100" class="object-cover rounded-lg">
                        <div class="product-info">
                            <h6 class="product-name mb-1 font-bold mt-1">${product.name}</h6>
                            <p class="product-price mb-2 text-red-600 font-semibold">${Number(product.price).toLocaleString('vi-VN')} VNĐ</p>
                            <a href="${product.link}" class="btn btn-sm btn-dark text-white product-link mr-2" target="_blank">Xem sản phẩm</a>
                        </div>
                    </div>
                </div>
            `;
}

function createProductTrending(product) {
    return `
        <div class="message bot-message">
            <div class="product-card flex gap-3 p-2 rounded shadow-sm bg-gray-50" style="max-width: 500px; border: 1px solid #ddd;">
                <img src="${product.image}" alt="${product.name}" width="100" height="100" class="object-cover rounded-lg">
                <div class="product-info">
                    <h6 class="product-name mb-1 font-bold mt-1">${product.name}</h6>
                    <p class="product-details mb-1 text-gray-600">Màu: ${product.color}, Kích thước: ${product.size}</p>
                    <p class="product-price mb-2 text-red-600 font-semibold">${Number(product.price).toLocaleString('vi-VN')} VNĐ</p>
                    <a href="${product.link}" class="btn btn-sm btn-dark text-white product-link mr-2" target="_blank">Xem sản phẩm</a>
                </div>
            </div>
        </div>
    `;
}

function createDetailedProductCard(product) {
    const imagesHtml = product.images.map((img, index) => `
                <img src="${img}" alt="${product.name} ${index + 1}" width="100" height="100" class="object-cover rounded-lg mb-2">
            `).join('');

    const variantsHtml = product.variants.map(variant => `
                <div class="mb-2">
                    <p><strong>Màu:</strong> ${variant.color}</p>
                    <p><strong>Kích thước:</strong> ${variant.size}</p>
                    <p><strong>Giá:</strong> ${Number(variant.price).toLocaleString('vi-VN')} VNĐ</p>
                    <p><strong>Tồn kho:</strong> ${variant.stock} sản phẩm</p>
                    <hr>
                </div>
            `).join('');

    return `
                <div class="message bot-message">
                    <div class="product-card flex flex-col gap-3 p-4 rounded shadow-sm bg-gray-50" style="max-width: 500px; border: 1px solid #ddd;">
                        <h6 class="product-name mb-2 font-bold text-lg">${product.name}</h6>
                        ${imagesHtml}
                        <div class="product-info">
                            <p class="mb-2"><strong>Mô tả:</strong> ${product.description}</p>
                            <div class="variants">
                                <h6 class="font-semibold mb-2">Các biến thể:</h6>
                                ${variantsHtml}
                            </div>
                            <a href="${product.link}" class="btn btn-sm btn-dark text-white product-link mt-2 inline-block mr-2" target="_blank">Xem sản phẩm</a>
                            ${product.variants.length === 0 ? 
                                '<p class="text-red-600 mt-2">Biến thể chưa được cấu hình. Vui lòng liên hệ hỗ trợ.</p>' : 
                                `<button onclick='showVariantModal(${JSON.stringify(product)})' class="btn btn-sm buy-button text-white mt-2 inline-block">Mua hàng</button>`}
                        </div>
                    </div>
                </div>
            `;
}

function showVariantModal(product) {
    selectedProduct = product;
    const modal = document.getElementById('variant-modal');
    const optionsDiv = document.getElementById('variant-options');
    const messagesDiv = document.getElementById('chat-messages');

    const validVariants = product.variants.filter(
        variant => variant.color !== 'Chưa xác định' &&
        variant.size !== 'Chưa xác định' &&
        variant.color !== 'undefined' &&
        variant.size !== 'undefined' &&
        variant.stock > 0
    );

    if (validVariants.length === 0) {
        messagesDiv.innerHTML +=
            `<div class="message bot-message"><div class="bubble">Không có biến thể hợp lệ. Vui lòng liên hệ hỗ trợ qua số 0123-456-789 hoặc email support@shop.com.</div></div>`;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        return;
    }

    optionsDiv.innerHTML = `
                <label class="block mb-2 font-semibold form-label">Chọn biến thể:</label></br>
                <select id="variant-select" name="variant" class="w-full p-2 border rounded">
                    ${validVariants.map(variant => `
                        <option value="${variant.variantId}" data-product-id="${variant.productId}">
                            Màu: ${variant.color}, Kích thước: ${variant.size} (${Number(variant.price).toLocaleString('vi-VN')} VNĐ, Còn: ${variant.stock})
                        </option>
                    `).join('')}
                </select>
            `;

    modal.style.display = 'flex';
}

function closeVariantModal() {
    const modal = document.getElementById('variant-modal');
    modal.style.display = 'none';
    selectedProduct = null;
}

function confirmBuy() {
    const select = document.getElementById('variant-select');

    if (!selectedProduct) {
        const messagesDiv = document.getElementById('chat-messages');
        messagesDiv.innerHTML +=
            `<div class="message bot-message"><div class="bubble">Không có sản phẩm được chọn. Vui lòng thử lại.</div></div>`;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        closeVariantModal();
        return;
    }

    let variantId, productId;
    if (selectedProduct.variants.length === 1) {
        variantId = selectedProduct.variants[0].variantId;
        productId = selectedProduct.variants[0].productId;
    } else {
        if (!select || !select.value) {
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML +=
                `<div class="message bot-message"><div class="bubble">Vui lòng chọn một biến thể hợp lệ.</div></div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            return;
        }
        variantId = select.value;
        productId = select.options[select.selectedIndex].getAttribute('data-product-id');
    }
    if (!select || !select.value) {
        console.log(variantId, productId);

    }

    fetch('/ai/add-to-cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                product_id: productId,
                variant_id: variantId,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML +=
                `<div class="message bot-message"><div class="bubble">${escapeHtml(data.message)}</div></div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            closeVariantModal();

            if (!data.success && data.redirect) {
                window.location.href = data.redirect;
            } else if (data.success && data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(() => {
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML +=
                `<div class="message bot-message"><div class="bubble">Lỗi khi thêm vào giỏ hàng. Vui lòng thử lại.</div></div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            console.log(variantId, productId);

            closeVariantModal();
        });
}
</script>