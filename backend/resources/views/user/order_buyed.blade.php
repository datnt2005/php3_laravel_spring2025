@extends('layouts.appUser')
@section('content')

@forelse($orders as $order)

<div class="container my-5">
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <!-- Hiển thị từng đơn hàng -->
        <div class="col-md-12">
            <div class="card h-100 shadow border-0 rounded-4 p-3 hover-effect">
                <div class="card-body">
                    <h5 class="card-title mb-3 text-primary">Mã Đơn Hàng: {{ $order->tracking_code }}</h5>
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Ngày Mua</th>
                                <th>Tổng Tiền</th>
                                <th>Địa Chỉ</th>
                                <th>Trạng Thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ date_format($order->created_at, 'd-m-Y') }}</td>
                                <td>{{ number_format($order->final_price) }} đ</td>
                                <td>
                                    <strong class="text-muted">{{ $order->address->name ?? 'N/A' }}</strong> | 
                                    {{ $order->address->phone ?? 'N/A' }}<br>
                                    <span class="address-full" 
                                          data-address-id="{{ $order->address->id ?? '' }}"
                                          data-province-id="{{ $order->address->province_id ?? '' }}"
                                          data-district-id="{{ $order->address->district_id ?? '' }}"
                                          data-ward-code="{{ $order->address->ward_code ?? '' }}">
                                        {{ $order->address->detail ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->status == 'pending')
                                        <span class="badge rounded-0 w-75 text-center bg-warning">Pending</span>
                                    @elseif($order->status == 'processing')
                                        <span class="badge rounded-0 w-75 text-center bg-info">Processing</span>
                                    @elseif($order->status == 'completed')
                                        <span class="badge rounded-0 w-75 text-center bg-success">Completed</span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="badge rounded-0 w-75 text-center bg-danger">Cancelled</span>
                                    @elseif($order->status == 'cancel')
                                        <span class="badge rounded-0 w-75 text-center bg-danger">Cancel</span>
                                    @elseif($order->status == 'shipping')
                                        <span class="badge rounded-0 w-75 text-center bg-primary">Shipping</span>
                                    @elseif($order->status == 'delivered')
                                        <span class="badge rounded-0 w-75 text-center bg-success">Delivered</span>
                                    @elseif($order->status == 'received')
                                        <span class="badge rounded-0 w-75 text-center bg-success">Received</span>
                                    @elseif($order->status == 'return')
                                        <span class="badge rounded-0 w-75 text-center bg-danger">Return</span>
                                    @elseif($order->status == 'ready_to_pick')
                                        <span class="badge rounded-0 w-75 text-center bg-info">Ready to pick</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center d-flex justify-content-end mt-3">
                        @if ($order->status == 'pending' || $order->status == 'processing' || $order->status == 'ready_to_pick')
                            <button type="button" onclick="cancelOrder({{ $order->id }})" 
                                    class="btn btn-dark rounded-0 px-4 py-2 mx-2">Hủy đơn hàng</button>
                        @elseif ($order->status == 'cancelled')
                            <button type="button" onclick="removeOrder({{ $order->id }})" 
                                    class="btn btn-dark rounded-0 px-4 py-2 mx-2">Xoá đơn hàng</button>
                        @endif
                        <button type="button" onclick="showOrderDetail({{ $order->id }})"
                                class="btn btn-transparent border rounded-0 px-4 py-2 me-2">Chi tiết</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="container my-5">
    <div class="empty-cart text-center">
        <i class="fa-solid fa-face-sad-tear" style="font-size: 150px; color: rgb(61, 61, 61);"></i>
        <p class="fw-bold text-center fs-5 mt-3">Chưa có đơn hàng nào</p>  
    </div>
</div>
@endforelse

<div id="overlay" class="overlay d-none"></div>
<div id="detailOrder" class="detailOrder-modal d-none w-75 mx-auto p-4 rounded shadow-lg bg-white" aria-hidden="true">
    <div class="password-modal-content position-relative">
        <button class="close btn position-absolute top-0 end-0" onclick="closeModal()">×</button>
        <h3 class="text-center tracking_order fw-medium mb-3">Chi tiết đơn hàng</h3>
        <div class="password-modal-body">
            <div class="detailOrder-list">
                <ul class="list-unstyled">
                    <li class="mt-1">
                        <span class="fw-bold">Địa chỉ giao hàng: </span>
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
                    <li class="fw-bold mt-1">Trạng Thái: <span class="fw-medium" id="status"></span></li>
                </ul>
            </div>
            <div class="detailOrder-item mt-3">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th></th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng tiền</th>
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
                <button type="button" class="btn btn-transparent border rounded-0 px-4 py-2 me-2" 
                        onclick="closeModal()">Hủy</button>
                <button type="button" onclick="reorder()" class="btn btn-dark rounded-0 px-4 py-2">Mua lại</button>
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
    display: none;
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

// Lấy tên tỉnh/thành phố
async function getProvinceName(provinceId) {
    try {
        const response = await fetch('/ghn/provinces', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
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
        const response = await fetch(`/ghn/districts/${provinceId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
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
        const response = await fetch(`/ghn/wards/${districtId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
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
            try {
                const provinceName = await getProvinceName(provinceId);
                const districtName = await getDistrictName(districtId, provinceId);
                const wardName = await getWardName(wardCode, districtId);
                element.textContent = `${detail}, ${wardName}, ${districtName}, ${provinceName}`;
            } catch (error) {
                element.textContent = `${detail}, Lỗi khi lấy thông tin địa chỉ`;
                showAlert('Không thể tải thông tin địa chỉ', 'danger');
            }
        } else {
            element.textContent = detail === 'N/A' ? 'Thông tin địa chỉ không đầy đủ' : `${detail}, Thông tin địa chỉ không đầy đủ`;
        }
    }
}

let currentOrderId = null;

async function showOrderDetail(id) {
    currentOrderId = id;
    const detailModal = document.getElementById('detailOrder');
    const overlay = document.getElementById('overlay');

    try {
        const response = await fetch(`/orders/detail/${id}`);
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Không thể lấy chi tiết đơn hàng (Mã lỗi: ${response.status})`);
        }
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Lỗi dữ liệu');
        }

        const order = data.order;

        document.querySelector('.tracking_order').innerText = `Chi tiết đơn hàng #${order.tracking_code}`;
        document.getElementById('note').innerText = order.note || 'Không có ghi chú';
        document.getElementById('username').innerText = order.address?.name || 'N/A';
        document.getElementById('phone').innerText = order.address?.phone || 'N/A';
        document.getElementById('detail').innerText = order.address?.detail || 'N/A';
        document.getElementById('code').innerText = order.tracking_code;
        document.getElementById('created_at').innerText = formatDate(order.created_at);
        document.getElementById('status').innerText = convertStatus(order.status);
        document.querySelector('.quantity-items').innerText = `${data.total_quantity} sản phẩm`;
        document.querySelector('.price-delivery').innerText = formatCurrency(order.shipping_fee);
        document.querySelector('.price-coupon').innerText = formatCurrency(order.discount_price);
        document.querySelector('.total-quantityInCart').innerText = formatCurrency(order.final_price);

        let addressText = 'Thông tin địa chỉ không đầy đủ';
        if (order.address && order.address.province_id && order.address.district_id && order.address.ward_code) {
            const provinceName = await getProvinceName(order.address.province_id);
            const districtName = await getDistrictName(order.address.district_id, order.address.province_id);
            const wardName = await getWardName(order.address.ward_code, order.address.district_id);
            addressText = `${wardName}, ${districtName}, ${provinceName}`;
        }
        document.getElementById('address').innerText = addressText;

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
        showAlert('Lỗi: ' + error.message, 'danger');
    }
}

function closeModal() {
    document.getElementById('detailOrder').classList.add('d-none');
    document.getElementById('overlay').classList.add('d-none');
}

function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function convertStatus(status) {
    switch (status) {
        case 'pending': return 'Chờ xử lý';
        case 'processing': return 'Đang xử lý';
        case 'completed': return 'Hoàn thành';
        case 'cancelled': return 'Đã hủy';
        case 'shipping': return 'Đang giao';
        case 'delivered': return 'Đã giao';
        case 'received': return 'Đã nhận';
        case 'failed': return 'Không thành công';
        case 'return': return 'Đang trả hàng';
        case 'ready_to_pick': return 'Chờ xử lý';
        default: return status;
    }
}

function reorder() {
    if (!currentOrderId) return;

    fetch(`/orders/reorder/${currentOrderId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/cart';
        } else {
            showAlert('Có lỗi xảy ra khi mua lại đơn hàng!', 'danger');
        }
    })
    .catch(err => {
        console.error('Lỗi mua lại:', err);
        showAlert('Lỗi khi mua lại đơn hàng!', 'danger');
    });
}

function cancelOrder(id) {
    const confirmCancel = confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');
    if (!confirmCancel) return;

    fetch(`/orders/cancel/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert('Có lỗi xảy ra khi hủy đơn hàng!', 'danger');
        }
    })
    .catch(err => {
        console.error('Lỗi hủy đơn hàng:', err);
        showAlert('Lỗi khi hủy đơn hàng!', 'danger');
    });
}

function removeOrder(id) {
    const confirmRemove = confirm('Bạn có chắc chắn muốn xóa đơn hàng này?');
    if (!confirmRemove) return;

    fetch(`/orders/remove/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            
            location.reload();
            showAlert(data.message, 'success');
        } else {
            showAlert('Có lỗi xảy ra khi xoá đơn hàng!', 'danger');
        }
    })
    .catch(err => {
        console.error('Lỗi hủy đơn hàng:', err);
        showAlert('Lỗi khi hủy đơn hàng!', 'danger');
    });
}
// Tải địa chỉ khi trang được load
document.addEventListener('DOMContentLoaded', () => {
    loadAddressList();
});
</script>

@endsection