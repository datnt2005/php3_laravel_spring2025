@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Orders</h3>
                </div>
                <form method="GET" class="m-3">
                    <select name="status" class="form-select w-25" onchange="this.form.submit()">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="shipping" {{ $status == 'shipping' ? 'selected' : '' }}>Shipping</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Code</th>
                            <th>Total</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>
                                <strong class="text-muted">{{ $order->address->name ?? 'N/A' }}</strong> |
                                {{ $order->address->phone ?? 'N/A' }}<br>
                                <span class="address-full" data-address-id="{{ $order->address->id ?? '' }}"
                                      data-province-id="{{ $order->address->province_id ?? '' }}"
                                      data-district-id="{{ $order->address->district_id ?? '' }}"
                                      data-ward-code="{{ $order->address->ward_code ?? '' }}">
                                    {{ $order->address->detail ?? '' }}
                                </span>
                            </td>
                            <td>{{ $order->tracking_code }}</td>
                            <td>{{ number_format($order->final_price) }} đ</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($order->status == 'cancel')
                                    <span class="badge bg-danger">Cancelled</span>
                                @elseif($order->status == 'shipping')
                                    <span class="badge bg-primary">Shipping</span>
                                @elseif($order->status == 'delivered')
                                    <span class="badge bg-success">Delivered</span>
                                @elseif($order->status == 'received')
                                    <span class="badge bg-success">Received</span>
                                @elseif($order->status == 'return')
                                    <span class="badge bg-danger">Return</span>
                                @elseif($order->status == 'ready_to_pick')
                                    <span class="badge bg-info">Ready to pick</span>

                                @endif
                            </td>
                            <td>
                                <button onclick="showOrderDetail({{ $order->id }})"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No orders found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="overlay" class="overlay d-none"></div>
<div id="detailOrder" class="detailOrder-modal d-none w-75 mx-auto p-4 rounded shadow-lg bg-white" aria-hidden="true">
    <div class="password-modal-content position-relative">
        <button class="close btn position-absolute top-0 end-0" onclick="closeModal()">×</button>
        <h3 class="text-center tracking_order fw-medium mb-3">Detail Order</h3>
        <div class="password-modal-body">
            <div class="detailOrder-list">
                <ul class="list-unstyled">
                    <li class="mt-1"><span class="fw-bold">Địa chỉ giao hàng: </span>
                        <p class="m-0">
                            <span id="username" class="fst-italic text-secondary"></span> |
                            <span class="phone-number text-secondary" id="phone"></span>
                        </p>
                        <p class="vilage-streetName m-0 text-secondary" id="detail"></p>
                        <p class="provine-district m-0 text-secondary" id="address"></p>
                    </li>
                    <li class="fw-bold mt-1">Mã đơn hàng: <span class="fw-medium" id="code"></span></li>
                    <li class="fw-bold mt-1">Ghi chú: <span class="fw-medium" id="note"></span></li>
                    <li class="fw-bold mt-1">Ngày đặt hàng: <span class="fw-medium" id="created_at"></span></li>
                    <form id="changeStatusForm" action="{{ route('admin.orders.changeStatus') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" id="form_order_id">
                        <select name="status" class="form-select w-25 mt-2" onchange="submitStatusForm(this)">
                            <option value="">Change Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipping">Shipping</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </form>
                </ul>
            </div>
            <div class="detailOrder-item mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th></th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="d-flex justify-content-end">
                    <div class="general-cart d-flex justify-content-between">
                        <div class="start-items">
                            <p class="fw-bold">Tổng sản phẩm:</p>
                            <p class="fw-bold">Vận chuyển:</p>
                            <p class="fw-bold">Giảm giá:</p>
                            <p class="fw-bold">Tổng tiền:</p>
                        </div>
                        <div class="end-items text-end">
                            <p class="quantity-items"></p>
                            <p class="price-delivery"></p>
                            <p class="price-coupon"></p>
                            <p class="total-quantityInCart"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-transparent border rounded-0 px-4 py-2 me-2" onclick="closeModal()">Hủy</button>
                <button type="button" onclick="submitStatusForm(document.querySelector('#changeStatusForm select'))" class="btn btn-dark rounded-0 px-4 py-2">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<div id="alerts-container"></div>

<style>
.detailOrder-modal {
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

.d-none {
    display: none !important;
}

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

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<script>
// Hàm hiển thị thông báo
function showAlert(message, type = 'danger') {
    const alertContainer = document.getElementById('alerts-container');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertContainer.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

async function getProvinceName(provinceId) {
    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            throw new Error('Không tìm thấy CSRF token trong trang');
        }
        const response = await fetch('/ghn/provinces', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
            }
        });
        const provinces = await response.json();
        const provinceData = Array.isArray(provinces) ? provinces : (provinces.data || []);
        const province = provinceData.find(p => p.ProvinceID == provinceId);
        return province ? province.ProvinceName : 'N/A';
    } catch (error) {
        console.error('Lỗi khi lấy tên tỉnh:', error);
        return 'N/A';
    }
}

// Lấy tên quận/huyện
async function getDistrictName(districtId, provinceId) {
    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            throw new Error('Không tìm thấy CSRF token trong trang');
        }
        const response = await fetch(`/ghn/districts/${provinceId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
            }
        });
        const districts = await response.json();
        const districtData = Array.isArray(districts) ? districts : (districts.data || []);
        const district = districtData.find(d => d.DistrictID == districtId);
        return district ? district.DistrictName : 'N/A';
    } catch (error) {
        console.error('Lỗi khi lấy tên quận:', error);
        return 'N/A';
    }
}

// Lấy tên xã/phường
async function getWardName(wardCode, districtId) {
    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            throw new Error('Không tìm thấy CSRF token trong trang');
        }
        const response = await fetch(`/ghn/wards/${districtId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
            }
        });
        const wards = await response.json();
        const wardData = Array.isArray(wards) ? wards : (wards.data || []);
        const ward = wardData.find(w => w.WardCode == wardCode);
        return ward ? ward.WardName : 'N/A';
    } catch (error) {
        console.error('Lỗi khi lấy tên xã:', error);
        return 'N/A';
    }
}
// Tải địa chỉ cho danh sách đơn hàng
async function loadAddressList() {
    const addressElements = document.querySelectorAll('.address-full');
    for (const element of addressElements) {
        const provinceId = element.dataset.provinceId;
        const districtId = element.dataset.districtId;
        const wardCode = element.dataset.wardCode;
        const detail = element.textContent.trim();

        if (provinceId && districtId && wardCode) {
            const provinceName = await getProvinceName(provinceId);
            const districtName = await getDistrictName(districtId, provinceId);
            const wardName = await getWardName(wardCode, districtId);
            element.textContent = `${detail}, ${wardName}, ${districtName}, ${provinceName}`;
        } else {
            element.textContent = detail ? `${detail}, Thông tin địa chỉ không đầy đủ` : 'Thông tin địa chỉ không đầy đủ';
        }
    }
}

// Hiển thị chi tiết đơn hàng
async function showOrderDetail(id) {
    const detailModal = document.getElementById('detailOrder');
    const overlay = document.getElementById('overlay');

    try {
        const response = await fetch(`/admin/orders/detail/${id}`);
        if (!response.ok) {
            throw new Error('Không thể lấy chi tiết đơn hàng');
        }
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Lỗi dữ liệu');
            
        }

        const order = data.order;

        // Hiển thị thông tin cơ bản
        document.querySelector('.tracking_order').innerText = `Chi tiết đơn hàng #${order.tracking_code}`;
        document.getElementById('note').innerText = order.note || 'Không có ghi chú';
        document.getElementById('username').innerText = order.address?.name || 'N/A';
        document.getElementById('phone').innerText = order.address?.phone || 'N/A';
        document.getElementById('detail').innerText = order.address?.detail || 'N/A';
        document.getElementById('code').innerText = order.tracking_code;
        document.getElementById('created_at').innerText = formatDate(order.created_at);
        document.querySelector('.quantity-items').innerText = `${data.total_quantity} sản phẩm`;
        document.querySelector('.price-delivery').innerText = formatCurrency(order.shipping_fee);
        document.querySelector('.price-coupon').innerText = formatCurrency(order.discount_price);
        document.querySelector('.total-quantityInCart').innerText = formatCurrency(order.final_price);
        document.querySelector('#form_order_id').value = order.id;

        // Lấy tên tỉnh, huyện, xã
        let addressText = 'Thông tin địa chỉ không đầy đủ';
        if (order.address && order.address.province_id && order.address.district_id && order.address.ward_code) {
            const provinceName = await getProvinceName(order.address.province_id);
            const districtName = await getDistrictName(order.address.district_id, order.address.province_id);
            const wardName = await getWardName(order.address.ward_code, order.address.district_id);
            addressText = `${wardName}, ${districtName}, ${provinceName}`;
        }
        document.getElementById('address').innerText = addressText;

        // Hiển thị sản phẩm
        const tbody = document.querySelector('#detailOrder tbody');
        tbody.innerHTML = '';
        data.items.forEach(item => {
            let attributesHtml = item.attributes.map(attr => `
                <p class="attribute fw-medium text-secondary mb-0">${attr.name}: ${attr.value}</p>
            `).join('');

            tbody.innerHTML += `
                <tr>
                    <td style="width: 100px" class="text-center">
                        <a href="/product/${item.slug}" target="_blank" class="text-decoration-none text-dark">
                            <img src="/storage/${item.image}" alt="${item.name}" class="img-fluid" width="50" height="50">
                        </a>
                    </td>
                    <td>
                        <a href="/product/${item.slug}" target="_blank" class="text-decoration-none text-dark">
                            <p class="fw-bold text-dark mb-0">${item.name}</p>
                            ${attributesHtml}
                        </a>
                    </td>
                    <td>${formatCurrency(item.price)}</td>
                    <td>${item.quantity}</td>
                    <td>${formatCurrency(item.price * item.quantity)}</td>
                </tr>
            `;
        });

        detailModal.classList.remove('d-none');
        overlay.classList.remove('d-none');
    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu đơn hàng:', error);
        showAlert('Lỗi khi lấy dữ liệu đơn hàng: ' + error.message, 'danger');
    }
}

// Đóng modal
function closeModal() {
    document.getElementById('detailOrder').classList.add('d-none');
    document.getElementById('overlay').classList.add('d-none');
}

// Định dạng tiền tệ
function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
}

// Định dạng ngày
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Submit form thay đổi trạng thái
function submitStatusForm(selectElement) {
    const form = document.getElementById('changeStatusForm');
    if (selectElement.value) {
        form.submit();
    }
}

// Tải địa chỉ khi trang được load
document.addEventListener('DOMContentLoaded', () => {
    loadAddressList();
});
</script>
@endsection