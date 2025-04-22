@extends('layouts.appUser')
@section('content')
<main>
    <div class="container mt-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="nav-link">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
            </ol>
        </nav>
    </div>
    <section id="checkout" class="mb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-checkout">
                        <h1 class="fs-2">THANH TOÁN</h1>
                        <form action="{{ route('checkout.create') }}" method="POST">
                            @csrf
                            <input type="hidden" name="total_price" id="total_price" value="{{ $totalPrice }}">
                            <input type="hidden" name="final_price" id="final_price" value="{{ $totalPrice }}">
                            <input type="hidden" name="discount_price" id="discount_price" value="{{ $discount_price ?? 0 }}">
                            <input type="hidden" name="discount_id" id="discount_id" value="{{ $discount_id ?? null }}">
                            <input type="hidden" name="shipping_fee" id="shipping_fee" value="0">
                            <input type="hidden" name="total_quantity" value="{{ $totalQuantity }}">
                            @foreach ($cartItems as $item)
                                <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
                            @endforeach

                            <div class="address-delivery">
                                <p class="fw-bold mb-2">1. Địa chỉ nhận hàng</p>
                                <input type="hidden" name="address_id" id="address_id" value="{{ $address ? $address->id : '' }}">
                                <div class="detail-address px-2">
                                    <div class="address-content">
                                        <p onclick="toggleAddressForm(event)" class="add-address nav-link d-flex align-items-center ms-2">
                                            <i class="fa-solid fa-plus fs-5"></i>
                                            <label class="ms-2 mb-1 text-primary">Thay đổi</label>
                                        </p>
                                        @if ($address)
                                        <p class="m-0">
                                            <span class="fst-italic text-secondary">{{ $address->name }}</span> |
                                            <span class="phone-number text-secondary">{{ $address->phone }}</span>
                                        </p>
                                        <p class="vilage-streetName m-0 text-secondary">{{ $address->detail }}</p>
                                        <p class="provine-district m-0 text-secondary" id="address-full-{{ $address->id }}"></p>
                                        @endif
                                    </div>
                                    <div class="noteOrder mt-3">
                                        <label for="note">Ghi chú</label>
                                        <textarea name="note" id="note" class="w-100 value-forgot"></textarea>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="delivery-price">
                                <p class="fw-bold mb-2">2. Vận chuyển</p>
                                <div class="d-flex justify-content-between px-2">
                                    <div class="input-checked">
                                        <input type="radio" name="delivery" id="delivery" required value="standard" checked onchange="calculateShippingFee()">
                                        <label class="fs-6">GHN - Tiêu chuẩn</label>
                                    </div>
                                    <p class="price delivery price-delivery">0đ</p>
                                </div>
                            </div>
                            <hr>
                            <div class="payment">
                                <p class="fw-bold mb-2">3. Phương thức thanh toán</p>
                                <div class="payment-method px-2">
                                    <div class="pay">
                                        <input type="radio" name="payment_method" value="cod" checked>
                                        <label class="fs-6 mx-2">Thanh toán khi nhận hàng</label>
                                    </div>
                                    <div class="vnPay mt-1">
                                        <input type="radio" name="payment_method" value="vnpay">
                                        <label class="fs-6 mx-2">Thanh toán VNPAY</label>
                                    </div>
                                    <div class="momo mt-1">
                                        <input type="radio" name="payment_method" value="momo">
                                        <label class="fs-6 mx-2">Thanh toán MOMO</label>
                                    </div>

                                </div>
                            </div>
                            <hr>
                            <div class="coupon">
                                <p class="fw-bold mb-2">4. Áp dụng mã giảm giá</p>
                                <div class="coupon-input px-2">
                                    <div id="couponForm">
                                        @csrf
                                        <input type="hidden" name="total_price" id="coupon_total_price" value="{{ $totalPrice }}">
                                        <input type="hidden" name="total_quantity" value="{{ $totalQuantity }}">
                                        <input type="search" name="code-discount" id="code-discount" placeholder="Nhập mã giảm giá" class="w-75">
                                        <button class="mx-2" type="button" name="submit" id="apply-discount">Sử dụng</button>
                                    </div>
                                </div>
                                <div class="coupon-apply px-2 d-flex justify-content-between">
                                    <p class="coupon-apply-message text-success mt-2" id="coupon-apply-message"></p>
                                    <span class="remove-coupon text-danger fw-bold mx-3 mt-2"></span>
                                </div>
                            </div>
                            <div class="px-2">
                                <button type="submit" class="btn-toCheckout w-100 mt-3 px-2 fw-bold">ĐẶT HÀNG</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="position-sticky">
                        <h2 class="fs-5 color-main">THÔNG TIN ĐƠN HÀNG</h2>
                        <table class="table table-cart">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cartItems as $item)
                                <tr>
                                    <td>
                                        <div class="cart-products d-flex align-items-center">
                                            <a href="{{ route('product.show', $item->product->slug) }}">
                                                <div class="image-products">
                                                    <img src="{{ asset('storage/' . $item->productVariant->image) }}" alt="" class="w-100">
                                                </div>
                                                <div class="product-content mx-2">
                                                    <a href="#" class="name-products text-secondary fw-bold text-decoration-none">{{ $item->product->name }}</a>
                                                    @foreach ($item->productVariant->attributes as $attribute)
                                                    @if ($attribute->attribute->name == 'color')
                                                    <p class="attribute fw-medium text-secondary mb-0">
                                                        {{ ucfirst($attribute->attribute->name) }}:
                                                        <span class="color-variant btn btn-sm rounded-circle border" style="padding: 7px; background-color: {{ $attribute->value->value }};"></span>
                                                    </p>
                                                    @else
                                                    <p class="attribute fw-medium text-secondary mb-0">
                                                        {{ ucfirst($attribute->attribute->name) }}: {{ $attribute->value->value }}
                                                    </p>
                                                    @endif
                                                    @endforeach
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price">{{ number_format($item->price) }} <span class="fw-bold fs-7 text-decoration-underline">đ</span></span>
                                    </td>
                                    <td>
                                        <span class="text-center">{{ $item->quantity }}</span>
                                    </td>
                                    <td>
                                        <span class="total-price" id="total-price-{{ $item->id }}">{{ number_format($item->price * $item->quantity) }}</span>
                                        <span class="fw-bold fs-7 text-decoration-underline">đ</span>
                                    </td>
                                </tr>
                                @empty
                                <div class="empty-cart">
                                    <i class="fa-solid fa-face-sad-tear" style="font-size: 50px; color:rgb(61, 61, 61); margin-left: 42%;"></i>
                                    <p class="text-center fs-5 mt-3 mx-3">Chưa có sản phẩm trong giỏ hàng</p>
                                </div>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-end">
                            <div class="general-cart d-flex justify-content-between">
                                <div class="start-items">
                                    <p class="fw-bold">Tổng sản phẩm:</p>
                                    <p class="fw-bold">Tổng tiền:</p>
                                    <p class="fw-bold">Vận chuyển:</p>
                                    <p class="fw-bold">Giảm giá:</p>
                                </div>
                                <div class="end-items text-end">
                                    <p class="quantity-products">{{ $totalQuantity }}</p>
                                    <p class="total-quantityInCart">{{ number_format($totalPrice) }}đ</p>
                                    <p class="price-shipping">0đ</p>
                                    <p class="price-coupon">- 0đ</p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="total d-flex justify-content-between p-2">
                            <span class="fw-bold color-main">THÀNH TIỀN</span>
                            <span class="final-price fw-bold">{{ number_format($totalPrice) }}đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div id="listAddress" class="address-modal d-none w-50 mx-auto p-4 rounded shadow-lg bg-white" aria-hidden="true">
        <div class="password-modal-content position-relative">
            <button class="close btn position-absolute top-0 end-0" onclick="closeModal()">×</button>
            <h3 class="text-center fw-medium mb-3">Địa chỉ của tôi</h3>
            <div class="password-modal-body">
                <form id="changeAddress">
                @foreach($cartItems as $item)
                    <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
                    <input type="hidden" name="quantity[{{ $item->id }}]" value="{{ $item->quantity }}">
                @endforeach
                    <div class="mb-3">
                        <p onclick="toggleAddToAddressForm(event)" class="btn btn-transparent border rounded-0 px-4 py-2">+ Thêm địa chỉ</p>
                    </div>
                    <div class="address-list">
                        @foreach($addresses as $address)
                        <div class="address-item d-flex align-items-start p-3 border rounded mb-3" data-id="{{ $address->id }}">
                            <input type="radio" class="form-check-input mt-1" name="address" value="{{ $address->id }}" required {{ $address->is_default ? 'checked' : '' }}>
                            <div class="info ms-3 flex-grow-1">
                                <p class="fw-bold mb-1">{{ $address->name }} <span class="text-muted fw-medium">| {{ $address->phone }}</span></p>
                                <p class="text-muted mb-1">{{ $address->detail }}</p>
                                <p class="text-muted" id="address-full-list-{{ $address->id }}"></p>
                            </div>
                            <div class="actions d-flex flex-column align-items-end">
                                <a href="#" onclick="toggleEditToAddressForm(event)" class="text-primary text-decoration-none">Cập nhật</a>
                                @if ($address->is_default)
                                <span class="badge bg-danger rounded-0 mt-2">Mặc định</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-transparent border rounded-0 px-4 py-2 me-2" onclick="closeModal()">Hủy</button>
                        <button type="submit" name="submit" class="btn btn-dark rounded-0 px-4 py-2">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="addToAddress" class="address-modal d-none w-50" aria-hidden="true">
        <div class="password-modal-content">
            <span class="close ms-4 mb-2" onclick="closeModal()">×</span>
            <h3 class="text-center fw-medium">Địa chỉ mới</h3>
            <div class="password-modal-body">
                <form id="addToAddressForm">
                    @csrf
                    <div class="row">
                        <div class="name-value mt-4 col-md-6">
                            <label class="form-label m-0">Tên <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="value-forgot d-block w-100 name" required placeholder="Nhập tên">
                        </div>
                        <div class="phone-value mt-4 col-md-6">
                            <label class="form-label m-0">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="phone" id="phone" class="value-forgot d-block w-100 phone" required placeholder="Nhập số điện thoại">
                        </div>
                    </div>
                    <div class="address-value mt-4">
                        <label class="form-label m-0">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                        <select id="province" onchange="fetchDistricts()" class="value-forgot d-block w-100 province" name="province_id" required>
                            <option value="">Chọn tỉnh/thành phố</option>
                        </select>
                    </div>
                    <div class="address-value mt-4">
                        <label class="form-label m-0">Quận/Huyện <span class="text-danger">*</span></label>
                        <select id="district" onchange="fetchWards()" class="value-forgot d-block w-100 district" name="district_id" disabled>
                            <option value="">Chọn quận/huyện</option>
                        </select>
                    </div>
                    <div class="address-value mt-4">
                        <label for="ward" class="form-label m-0">Xã/Phường <span class="text-danger">*</span></label>
                        <select id="ward" onchange="calculateShippingFee()" class="value-forgot d-block w-100 ward" name="ward_code" disabled>
                            <option value="">Chọn xã/phường</option>
                        </select>
                    </div>
                    <div class="address-value mt-4">
                        <label for="detail" class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                        <input type="text" id="detail" name="detail" class="value-forgot d-block w-100 detail" required placeholder="Nhập số nhà, tên đường">
                    </div>
                    <div class="address-value mt-4">
                        <input type="checkbox" name="default" id="default" value="1" class="form-check-input mt-1">
                        <label for="default" class="form-label ms-2">Đặt làm địa chỉ mặc định</label>
                    </div>
                    @if(session('error'))
                    <span class="text-danger fs-6 mt-2">{{ session('error') }}</span>
                    @endif
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-transparent border rounded-0 px-4 py-2 me-2" onclick="backToListAddress()">Trở lại</button>
                        <button type="submit" name="submit" class="btn btn-dark rounded-0 px-4 py-2">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="editAddress" class="address-modal d-none w-50" aria-hidden="true">
        <div class="password-modal-content">
            <span class="close ms-4 mb-2" onclick="closeModal()">×</span>
            <h3 class="text-center fw-medium">Cập nhật địa chỉ</h3>
            <div class="password-modal-body">
                <form id="editToAddressForm">
                    @csrf
                    <input type="hidden" name="address_id" id="edit_address_id">
                    <div class="row">
                        <div class="name-value mt-4 col-md-6">
                            <label class="form-label m-0">Tên <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="edit_username" class="value-forgot d-block w-100 name" required placeholder="Nhập tên">
                        </div>
                        <div class="phone-value mt-4 col-md-6">
                            <label class="form-label m-0">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="phone" id="edit_phone" class="value-forgot d-block w-100 phone" required placeholder="Nhập số điện thoại">
                        </div>
                    </div>
                    <div class="address-value mt-4">
                        <label class="form-label m-0">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                        <select id="edit_province" onchange="fetchDistricts('edit_')" class="value-forgot d-block w-100 province" name="province_id" required>
                            <option value="">Chọn tỉnh/thành phố</option>
                        </select>
                    </div>
                    <div class="address-value mt-4">
                        <label class="form-label m-0">Quận/Huyện <span class="text-danger">*</span></label>
                        <select id="edit_district" onchange="fetchWards('edit_')" class="value-forgot d-block w-100 district" name="district_id" disabled>
                            <option value="">Chọn quận/huyện</option>
                        </select>
                    </div>
                    <div class="address-value mt-4">
                        <label for="edit_ward" class="form-label m-0">Xã/Phường <span class="text-danger">*</span></label>
                        <select id="edit_ward" onchange="calculateShippingFee('edit_')" class="value-forgot d-block w-100 ward" name="ward_code" disabled>
                            <option value="">Chọn xã/phường</option>
                        </select>
                    </div>
                    <div class="address-value mt-4">
                        <label for="edit_detail" class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                        <input type="text" id="edit_detail" name="detail" class="value-forgot d-block w-100 detail" required placeholder="Nhập số nhà, tên đường">
                    </div>
                    <div class="address-value mt-4">
                        <input type="checkbox" name="default" id="edit_default" value="1" class="form-check-input mt-1">
                        <label for="edit_default" class="form-label ms-2">Đặt làm địa chỉ mặc định</label>
                    </div>
                    @if(session('error'))
                    <span class="text-danger fs-6 mt-2">{{ session('error') }}</span>
                    @endif
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-transparent border rounded-0 px-4 py-2 me-2" onclick="backToListAddress()">Trở lại</button>
                        <button type="submit" name="submit" class="btn btn-dark rounded-0 px-4 py-2">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="overlay" class="overlay d-none"></div>
    <div id="alerts-container"></div>
</main>

<style>
input[name="email"] { display: none; }
.address-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    z-index: 1002;
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
.d-none { display: none; }
.close { float: right; font-size: 24px; cursor: pointer; }
.address-list { max-height: 400px; overflow-y: auto; padding-right: 10px; }
.alert {
    position: fixed;
    top: 115px;
    left: 42%;
    z-index: 1000;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    width: 370px;
}
.alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
select { padding: 5px; width: 100%; }
</style>

<script>
function showAlert(message, type = 'danger') {
    const alertContainer = document.getElementById('alerts-container');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertContainer.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

function toggleAddToAddressForm(event) {
    event.preventDefault();
    const addToAddress = document.getElementById('addToAddress');
    const listAddress = document.getElementById('listAddress');
    const overlay = document.getElementById('overlay');
    addToAddress.classList.remove('d-none');
    listAddress.classList.add('d-none');
    overlay.classList.remove('d-none');
    fetchProvinces();
}

function toggleEditToAddressForm(event) {
    event.preventDefault();
    const editAddress = document.getElementById('editAddress');
    const listAddress = document.getElementById('listAddress');
    const overlay = document.getElementById('overlay');
    const addressItem = event.target.closest('.address-item');
    const addressId = addressItem.getAttribute('data-id');

    editAddress.classList.remove('d-none');
    listAddress.classList.add('d-none');
    overlay.classList.remove('d-none');

    const addressInfo = addressItem.querySelector('.info');
    document.getElementById('edit_address_id').value = addressId;
    document.getElementById('edit_username').value = addressInfo.querySelector('.fw-bold').textContent.split(' | ')[0];
    document.getElementById('edit_phone').value = addressInfo.querySelector('.text-muted.fw-medium').textContent.split(' | ')[1];
    document.getElementById('edit_detail').value = addressInfo.querySelectorAll('.text-muted')[1].textContent;

    fetchAddressDetails(addressId, 'edit_');
}

function toggleAddressForm(event) {
    event.preventDefault();
    const listAddress = document.getElementById('listAddress');
    const addToAddress = document.getElementById('addToAddress');
    const overlay = document.getElementById('overlay');
    listAddress.classList.remove('d-none');
    addToAddress.classList.add('d-none');
    overlay.classList.remove('d-none');
    loadAddressListDetails();
}

function closeModal() {
    document.getElementById('listAddress').classList.add('d-none');
    document.getElementById('addToAddress').classList.add('d-none');
    document.getElementById('editAddress').classList.add('d-none');
    document.getElementById('overlay').classList.add('d-none');
}

function backToListAddress() {
    document.getElementById('addToAddress').classList.add('d-none');
    document.getElementById('editAddress').classList.add('d-none');
    document.getElementById('listAddress').classList.remove('d-none');
    document.getElementById('overlay').classList.remove('d-none');
}

document.getElementById('overlay').addEventListener('click', closeModal);

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('showList') === 'true') {
        toggleAddressForm(event);
    }
    @if ($address)
    fetchAddressDetails('{{ $address->id }}');
    calculateShippingFee();
    @endif
});

async function fetchProvinces(prefix = '') {
    try {
        const response = await fetch('/ghn/provinces', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        const provinceSelect = document.getElementById(prefix + 'province');

        if (!provinceSelect) {
            showAlert(`Không tìm thấy dropdown tỉnh/thành phố!`, 'danger');
            return;
        }

        let provinces = Array.isArray(data) ? data : (data.data || []);
        provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
        provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.ProvinceID;
            option.textContent = province.ProvinceName;
            provinceSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Lỗi khi tải danh sách tỉnh/thành phố:', error);
        showAlert('Lỗi khi tải danh sách tỉnh/thành phố!', 'danger');
    }
}

async function fetchDistricts(prefix = '') {
    const provinceId = document.getElementById(prefix + 'province').value;
    const districtSelect = document.getElementById(prefix + 'district');
    const wardSelect = document.getElementById(prefix + 'ward');

    districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
    districtSelect.disabled = true;
    wardSelect.disabled = true;

    if (!provinceId) return;

    try {
        const response = await fetch(`/ghn/districts/${provinceId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        let districts = Array.isArray(data) ? data : (data.data || []);
        districts.forEach(district => {
            const option = document.createElement('option');
            option.value = district.DistrictID;
            option.textContent = district.DistrictName;
            districtSelect.appendChild(option);
        });
        districtSelect.disabled = false;
    } catch (error) {
        console.error('Lỗi khi tải danh sách quận/huyện:', error);
        showAlert('Lỗi khi tải danh sách quận/huyện!', 'danger');
    }
}

async function fetchWards(prefix = '') {
    const districtId = document.getElementById(prefix + 'district').value;
    const wardSelect = document.getElementById(prefix + 'ward');

    wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
    wardSelect.disabled = true;

    if (!districtId) return;

    try {
        const response = await fetch(`/ghn/wards/${districtId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        let wards = Array.isArray(data) ? data : (data.data || []);
        wards.forEach(ward => {
            const option = document.createElement('option');
            option.value = ward.WardCode;
            option.textContent = ward.WardName;
            wardSelect.appendChild(option);
        });
        wardSelect.disabled = false;
    } catch (error) {
        console.error('Lỗi khi tải danh sách xã/phường:', error);
        showAlert('Lỗi khi tải danh sách xã/phường!', 'danger');
    }
}

async function fetchAddressDetails(addressId, prefix = '') {
    try {
        const response = await fetch(`/address/details/${addressId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        if (data.status === 'success') {
            const { province_id, district_id, ward_code } = data.address;
            if (prefix) {
                const provinceSelect = document.getElementById(prefix + 'province');
                const districtSelect = document.getElementById(prefix + 'district');
                const wardSelect = document.getElementById(prefix + 'ward');

                await fetchProvinces(prefix);
                provinceSelect.value = province_id;
                await fetchDistricts(prefix);
                districtSelect.value = district_id;
                await fetchWards(prefix);
                wardSelect.value = ward_code;
            } else {
                const provinceName = await getProvinceName(province_id);
                const districtName = await getDistrictName(district_id, province_id);
                const wardName = await getWardName(ward_code, district_id);
                const fullAddress = `${wardName}, ${districtName}, ${provinceName}`;
                document.getElementById(`address-full-${addressId}`).textContent = fullAddress;
                document.getElementById(`address-full-list-${addressId}`).textContent = fullAddress;
            }
        } else {
            showAlert('Không thể tải chi tiết địa chỉ!', 'danger');
        }
    } catch (error) {
        console.error('Lỗi khi tải chi tiết địa chỉ:', error);
    }
}

async function getProvinceName(provinceId) {
    try {
        const response = await fetch('/ghn/provinces', {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        const provinces = await response.json();
        const provinceData = Array.isArray(provinces) ? provinces : (provinces.data || []);
        const province = provinceData.find(p => p.ProvinceID == provinceId);
        return province ? province.ProvinceName : '';
    } catch (error) {
        console.error('Lỗi khi lấy tên tỉnh:', error);
        return '';
    }
}

async function getDistrictName(districtId, provinceId) {
    try {
        const response = await fetch(`/ghn/districts/${provinceId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        const districts = await response.json();
        const districtData = Array.isArray(districts) ? districts : (districts.data || []);
        const district = districtData.find(d => d.DistrictID == districtId);
        return district ? district.DistrictName : '';
    } catch (error) {
        console.error('Lỗi khi lấy tên quận:', error);
        return '';
    }
}

async function getWardName(wardCode, districtId) {
    try {
        const response = await fetch(`/ghn/wards/${districtId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        const wards = await response.json();
        const wardData = Array.isArray(wards) ? wards : (wards.data || []);
        const ward = wardData.find(w => w.WardCode == wardCode);
        return ward ? ward.WardName : '';
    } catch (error) {
        console.error('Lỗi khi lấy tên xã:', error);
        return '';
    }
}

async function loadAddressListDetails() {
    const addressItems = document.querySelectorAll('.address-item');
    addressItems.forEach(item => {
        const addressId = item.getAttribute('data-id');
        fetchAddressDetails(addressId, '');
    });
}

async function calculateShippingFee(prefix = '') {
    let districtId, wardId;

    if (prefix) {
        districtId = document.getElementById(prefix + 'district').value;
        wardId = document.getElementById(prefix + 'ward').value;
    } else {
        const addressId = document.getElementById('address_id').value;
        if (!addressId) {
            document.querySelector('.price-delivery').textContent = '0đ';
            updateFinalPrice(0);
            return;
        }
        try {
            const response = await fetch(`/address/details/${addressId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            });
            const data = await response.json();
            if (data.status === 'success') {
                districtId = data.address.district_id;
                wardId = data.address.ward_code;
            } else {
                showAlert('Không thể lấy thông tin địa chỉ để tính phí ship!', 'danger');
                document.querySelector('.price-delivery').textContent = '0đ';
                updateFinalPrice(0);
                return;
            }
        } catch (error) {
            showAlert('Lỗi khi lấy chi tiết địa chỉ!', 'danger');
            document.querySelector('.price-delivery').textContent = '0đ';
            updateFinalPrice(0);
            return;
        }
    }

    if (!districtId || !wardId) {
        document.querySelector('.price-delivery').textContent = '0đ';
        updateFinalPrice(0);
        showAlert('Vui lòng chọn đầy đủ quận/huyện và xã/phường!', 'danger');
        return;
    }

    const payload = {
        to_district_id: parseInt(districtId),
        to_ward_code: wardId,
        service_id: 53321,
        weight: 1000,
        height: 10,
        length: 20,
        width: 15
    };

    try {
        const response = await fetch('/ghn/shipping-fee', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Phản hồi từ server không hợp lệ: ${response.status}`);
        }

        const data = await response.json();
        if (data.status === 'success') {
            const shippingFee = data.shipping_fee;
            document.querySelector('.price-delivery').textContent = `${shippingFee.toLocaleString()}đ`;
            document.getElementById('shipping_fee').value = shippingFee;
            updateFinalPrice(shippingFee);
        } else {
            showAlert(data.message || 'Không thể tính phí vận chuyển!', 'danger');
            document.querySelector('.price-delivery').textContent = '0đ';
            updateFinalPrice(0);
        }
    } catch (error) {
        console.error('Lỗi khi tính phí ship:', error);
        showAlert('Đã xảy ra lỗi khi tính phí vận chuyển: ' + error.message, 'danger');
        document.querySelector('.price-delivery').textContent = '0đ';
        updateFinalPrice(0);
    }
}

function updateFinalPrice(shippingFee) {
    const totalPriceElement = document.querySelector('.total-quantityInCart');
    const shippingFeeElement = document.querySelector('.price-shipping');
    const finalPriceElement = document.querySelector('.final-price');
    const discountElement = document.querySelector('.price-coupon');

    const totalPrice = parseInt(totalPriceElement.textContent.replace('đ', '').replace(/,/g, ''));
    const discount = parseInt(discountElement.textContent.replace('đ', '').replace('-', '').replace(/,/g, '')) || 0;

    const finalPrice = totalPrice + shippingFee - discount;

    shippingFeeElement.textContent = `${shippingFee.toLocaleString()}đ`;
    finalPriceElement.textContent = `${finalPrice.toLocaleString()}đ`;
    document.getElementById('final_price').value = finalPrice; // Cập nhật giá trị final_price vào input hidden
}

document.getElementById('addToAddressForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const addressData = {
        name: document.getElementById('username').value,
        phone: document.getElementById('phone').value,
        province_id: document.getElementById('province').value,
        district_id: document.getElementById('district').value,
        ward_code: document.getElementById('ward').value,
        detail: document.getElementById('detail').value,
        is_default: document.getElementById('default').checked ? 1 : 0,
        _token: "{{ csrf_token() }}"
    };

    if (!addressData.name || !addressData.phone || !addressData.province_id || !addressData.district_id || !addressData.ward_code || !addressData.detail) {
        showAlert('Vui lòng nhập đầy đủ thông tin địa chỉ!', 'danger');
        return;
    }

    try {
        const response = await fetch("{{ route('address.create') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify(addressData)
        });
        const data = await response.json();

        if (data.status === 'success') {
            showAlert(data.message, 'success');
            window.location.href = window.location.pathname + '?showList=true';
        } else {
            showAlert(data.message || 'Có lỗi xảy ra khi thêm địa chỉ', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Đã có lỗi xảy ra khi thêm địa chỉ', 'danger');
    }
});

document.getElementById('editToAddressForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const addressData = {
        id: document.getElementById('edit_address_id').value,
        name: document.getElementById('edit_username').value,
        phone: document.getElementById('edit_phone').value,
        province_id: document.getElementById('edit_province').value,
        district_id: document.getElementById('edit_district').value,
        ward_code: document.getElementById('edit_ward').value,
        detail: document.getElementById('edit_detail').value,
        is_default: document.getElementById('edit_default').checked ? 1 : 0,
        _token: "{{ csrf_token() }}"
    };

    try {
        const response = await fetch("{{ route('address.update') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify(addressData)
        });
        const data = await response.json();

        if (data.status === 'success') {
            showAlert(data.message, 'success');
            window.location.href = window.location.pathname + '?showList=true';
        } else {
            showAlert(data.message || 'Có lỗi xảy ra khi cập nhật địa chỉ', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Đã có lỗi xảy ra khi cập nhật địa chỉ', 'danger');
    }
});

document.getElementById('changeAddress').addEventListener('submit', function(event) {
    event.preventDefault();

    const selectedAddress = document.querySelector('input[name="address"]:checked');

    if (!selectedAddress) {
        showAlert('Vui lòng chọn một địa chỉ!', 'danger');
        return;
    }

    const addressId = selectedAddress.value;

    // Lấy tất cả các input selected_items[]
    const selectedItems = document.querySelectorAll('input[name="selected_items[]"]');
    const quantities = document.querySelectorAll('input[name^="quantity["]');

    // Gộp lại thành query string
    const params = new URLSearchParams();
    params.append('address_id', addressId);

    selectedItems.forEach(input => {
        params.append('selected_items[]', input.value);
    });

    quantities.forEach(input => {
        params.append(input.name, input.value); // tên dạng quantity[127]
    });

    // Redirect
    window.location.href = `/checkout?${params.toString()}`;
});


document.getElementById('apply-discount').addEventListener('click', function() {
    const codeDiscount = document.getElementById('code-discount').value;
    const totalPrice = document.getElementById('coupon_total_price').value;
    if (!codeDiscount) {
        showAlert('Vui lòng nhập mã giảm giá!', 'danger');
        return;
    }

    fetch("{{ route('checkout.apply-coupon') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            code_discount: codeDiscount,
            total_price: totalPrice,
            _token: "{{ csrf_token() }}"
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message, 'success');
            document.querySelector('.price-coupon').textContent = `-${data.discount_price.toLocaleString()}đ`;
            document.getElementById('discount_price').value = data.discount_price;
            document.getElementById('discount_id').value = data.discount_id;
            updateFinalPrice(parseFloat(document.getElementById('shipping_fee').value));
            document.querySelector('.coupon-apply-message').textContent = `Mã giảm giá đã được áp dụng: -${data.discount_price.toLocaleString()}đ`;
            document.querySelector('.remove-coupon').innerHTML = '<a href="#" class="remove-coupon text-danger text-decoration-none">Bỏ dùng</a>';
        } else {
            showAlert(data.message || 'Mã giảm giá không hợp lệ!', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Đã có lỗi xảy ra khi áp dụng mã giảm giá', 'danger');
    });
});

document.addEventListener('click', function(event) {
    if (event.target.classList.contains('remove-coupon')) {
        event.preventDefault();
        document.querySelector('.price-coupon').textContent = '- 0đ';
        document.getElementById('discount_price').value = 0;
        document.getElementById('discount_id').value = '';
        updateFinalPrice(parseFloat(document.getElementById('shipping_fee').value));
        document.querySelector('.coupon-apply-message').textContent = '';
        document.querySelector('.remove-coupon').innerHTML = '';
        showAlert('Đã bỏ áp dụng mã giảm giá', 'success');
    }
});
</script>
@endsection