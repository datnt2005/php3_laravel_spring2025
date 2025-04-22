@extends('layouts.appUser')
@section('content')
<main>
    <div class="container mt-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/" class="nav-link">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
            </ol>
        </nav>
    </div>

    <section id="cart" class="py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    @if(!empty($cart))
                    @if (!$cartItems->isEmpty())

                    <form id="cart-form" action="{{ route('checkout.index') }}" method="GET">
                        <table class="table table-cart">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all" class="me-2">
                                    </th>
                                    <th>Sản phẩm</th>
                                    <th></th>
                                    <th>Số lượng</th>
                                    <th>Giá</th>
                                    <th>Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cartItems as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}"
                                            class="item-checkbox">
                                    </td>
                                    <td>
                                        <div class="cart-products d-flex align-items-center">
                                            <a href="{{ route('product.show', $item->product->slug) }}">
                                                <div class="image-products">
                                                    <img src="{{ asset('storage/' . $item->productVariant->image) }}"
                                                        alt="" class="w-100">
                                                </div>
                                                <div class="product-content mx-2">
                                                    <a href="#"
                                                        class="name-products text-secondary fw-bold text-decoration-none">{{ $item->product->name }}</a>
                                                    @foreach ($item->productVariant->attributes as $attribute)
                                                    @if ($attribute->attribute->name == 'color')
                                                    <p class="attribute fw-medium text-secondary mb-0">
                                                        {{ ucfirst($attribute->attribute->name) }}:
                                                        <span class="color-variant btn btn-sm rounded-circle border"
                                                            style="padding: 7px; background-color: {{ $attribute->value->value }};"></span>
                                                    </p>
                                                    @else
                                                    <p class="attribute fw-medium text-secondary mb-0">
                                                        {{ ucfirst($attribute->attribute->name) }}:
                                                        {{ $attribute->value->value }}
                                                    </p>
                                                    @endif
                                                    @endforeach
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="remove-cart d-flex align-items-center">
                                            <form action="{{ route('cart.delete', $item->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-transparent border-0 fw-bold text-danger">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="quantity">
                                            <button type="button" class="prev btn-minus"
                                                data-id="{{ $item->id }}">-</button>
                                            <input type="text" min="1"
                                                max="{{ $item->productVariant->quantityProduct }}"
                                                class="quantity-cart quantity-input" name="quantity"
                                                id="quantity-{{ $item->id }}" data-id="{{ $item->id }}"
                                                data-price="{{ $item->price }}" value="{{ $item['quantity'] }}">
                                            <button type="button" class="pluss btn-plus"
                                                data-id="{{ $item->id }}">+</button>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price">
                                            {{ number_format($item->price) }}
                                            <span class="fw-bold fs-7 text-decoration-underline">đ</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="total-price" id="total-price-{{ $item->id }}">
                                            {{ number_format($item->price * $item->quantity) }}
                                        </span>
                                        <span class="fw-bold fs-7 text-decoration-underline">đ</span>
                                    </td>
                                </tr>
                                @empty
                                <div class="empty-cart">
                                    <i class="fa-solid fa-face-sad-tear"
                                        style="font-size: 150px; color:rgb(61, 61, 61); margin-left: 42%;"></i>
                                    <p class="fw-bold text-center fs-5 mt-3 mx-3">Chưa có sản phẩm trong giỏ hàng</p>
                                </div>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            <div class="general-cart d-flex justify-content-between">
                                <div class="start-items">
                                    <p class="fw-bold">Tổng sản phẩm:</p>
                                    <p class="fw-bold color-main">TẠM TÍNH</p>
                                </div>
                                <div class="end-items text-end">
                                    <p class="quantity-products">0</p>
                                    <p class="total-quantityInCart">
                                        <span id="total_price">0</span>
                                        <span class="fw-bold fs-7 text-decoration-underline">đ</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3 mb-4">
                            <div class="to-shop">
                                <a href="/shop"
                                    class="text-decoration-none text-center toCheck py-2 px-4 text-dark fw-bold">
                                    TIẾP TỤC MUA HÀNG
                                </a>
                            </div>
                            <div class="to-checkout">
                                <button type="submit"
                                    class="toCheck text-decoration-none text-center py-2 px-5 btn border-0 rounded-0 btn-dark text-white fw-bold">ĐẶT
                                    HÀNG</button>
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="empty-cart">
                        <i class="fa-solid fa-face-sad-tear"
                            style="font-size: 100px; color:rgb(85, 85, 85); margin-left: 45%;"></i>
                        <p class="fw-bold text-center fs-5 mt-3 mx-3 text-muted">Chưa có sản phẩm trong giỏ hàng</p>
                    </div>
                    @endif
                    @else
                    <div class="empty-cart">
                        <i class="fa-solid fa-face-sad-tear"
                            style="font-size: 100px; color:rgb(85, 85, 85); margin-left: 45%;"></i>
                        <p class="fw-bold text-center fs-5 mt-3 mx-3 text-muted">Chưa có sản phẩm trong giỏ hàng</p>
                    </div>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="img-cart">
                        <img src="../../assets/images/image-cart.jpg" alt="" class="w-100">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div id="alerts-container"></div>
</main>
<style>
.alert {
    position: fixed;
    top: 115px;
    left: 42%;
    z-index: 1000;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    width: 320px;
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
document.addEventListener('DOMContentLoaded', function() {
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alerts-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertContainer.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    function updateTotalPrice(id) {
        let quantityInput = document.getElementById('quantity-' + id);
        let price = parseFloat(quantityInput.dataset.price);
        let quantity = parseInt(quantityInput.value) || 1;
        let maxQuantity = parseInt(quantityInput.getAttribute('max'));

        if (quantity > maxQuantity) {
            quantity = maxQuantity;
            showAlert(`Chỉ còn ${maxQuantity} sản phẩm trong kho.`, 'danger');
        }

        quantityInput.value = quantity;
        document.getElementById('total-price-' + id).textContent = (price * quantity).toLocaleString('vi-VN');
        updateCartTotal();
        updateCartItem(id, quantity);
        toggleButtonState(id, quantity, maxQuantity);
    }

    function updateCartTotal() {
        let totalQuantity = 0;
        let totalPrice = 0;
        document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
            let input = document.querySelector(`.quantity-input[data-id="${checkbox.value}"]`);
            let quantity = parseInt(input.value, 10);
            let price = parseFloat(input.dataset.price);
            totalQuantity += quantity;
            totalPrice += price * quantity;
        });
        document.querySelector('.quantity-products').textContent = totalQuantity;
        document.querySelector('.total-quantityInCart span').textContent = totalPrice.toLocaleString('vi-VN');
    }

    function updateCartItem(id, quantity) {
        fetch(`/cart/update/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateCartTotal();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => console.error('Lỗi:', error));
    }

    function toggleButtonState(id, quantity, maxQuantity) {
        let minusBtn = document.querySelector(`.btn-minus[data-id="${id}"]`);
        let plusBtn = document.querySelector(`.btn-plus[data-id="${id}"]`);

        minusBtn.disabled = quantity <= 1;
        plusBtn.disabled = quantity >= maxQuantity;
    }

    // Select All Checkbox
    const selectAll = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

    selectAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateCartTotal();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAll.checked = false;
            } else if (document.querySelectorAll('.item-checkbox:checked').length ===
                itemCheckboxes.length) {
                selectAll.checked = true;
            }
            updateCartTotal();
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        let id = input.dataset.id;
        let maxQuantity = parseInt(input.getAttribute('max'));
        toggleButtonState(id, input.value, maxQuantity);

        input.addEventListener('input', function() {
            updateTotalPrice(id);
        });
    });

    document.querySelectorAll('.btn-minus').forEach(button => {
        button.addEventListener('click', function() {
            let id = this.dataset.id;
            let input = document.getElementById('quantity-' + id);
            let quantity = parseInt(input.value) || 1;
            let maxQuantity = parseInt(input.getAttribute('max'));

            if (quantity > 1) {
                quantity--;
                input.value = quantity;
                updateTotalPrice(id);
            }
        });
    });

    document.querySelectorAll('.btn-plus').forEach(button => {
        button.addEventListener('click', function() {
            let id = this.dataset.id;
            let input = document.getElementById('quantity-' + id);
            let quantity = parseInt(input.value) || 1;
            let maxQuantity = parseInt(input.getAttribute('max'));

            if (quantity < maxQuantity) {
                quantity++;
                input.value = quantity;
                updateTotalPrice(id);
            } else {
                showAlert(`Chỉ còn ${maxQuantity} sản phẩm trong kho.`, 'danger');
            }
        });
    });

    // Validate checkout
    document.getElementById('cart-form').addEventListener('submit', function(e) {
        if (document.querySelectorAll('.item-checkbox:checked').length === 0) {
            e.preventDefault();
            showAlert('Vui lòng chọn ít nhất một sản phẩm để đặt hàng.', 'danger');
        }
    });
});
</script>
@endsection