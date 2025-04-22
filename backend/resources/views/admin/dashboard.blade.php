@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <!-- Row for the cards -->
    <div class="row mb-3">
        <div class="card-container">
            <div class="card">
                <h2>Tổng Doanh Thu</h2>
                <p id="totalRevenue">{{ number_format($totalRevenue) }}</p>
            </div>
            <div class="card">
                <h2>Tổng Lợi Nhuận</h2>
                <p id="totalProfit">{{ number_format($totalProfit) }}</p>
            </div>
            <div class="card">
                <h2>Tổng Đơn Hàng</h2>
                <p id="totalOrders">{{ number_format($totalOrders) }}</p>
            </div>
            <div class="card">
                <h2>Sản Phẩm Đã Bán</h2>
                <p id="totalSoldProducts">{{ number_format($totalSoldProducts) }}</p>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Biểu đồ lợi nhuận hàng ngày -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Lợi Nhuận Theo Ngày</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="profitChart"></canvas>
                        <h5 class="mt-3">Tổng lợi nhuận: <span id="total-profit-daily" class="fw-bold">{{ number_format($totalProfit) }}</span></h5>
                    </div>
                </div>
            </div>
            <!-- Biểu đồ tồn kho -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tồn Kho Sản Phẩm</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="stockChart"></canvas>
                        <h5 class="mt-3">Tổng số lượng sản phẩm tồn kho: <span id="total-stock" class="fw-bold">{{ number_format($totalStock) }}</span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Row for the charts -->
        <div class="row mt-3">
            <!-- Order Status Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Đơn Hàng Theo Trạng Thái</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="orderStatusChart" style="max-width: 600px; max-height: 600px;"></canvas>
                    </div>
                </div>
            </div>
            <!-- Best Selling Products Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Sản Phẩm Bán Chạy</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="bestSellingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thêm Chart.js từ CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Dữ liệu từ controller Laravel
const orderDates = @json($orderDates);
const dailyProfits = @json(array_values($dailyProfits)); // Lợi nhuận theo ngày
const productNames = @json($productNames);
const stockCounts = @json($stockCounts);
const statusLabels = @json($statusLabels);
const statusCounts = @json($statusCounts);
const bestSellingNames = @json($bestSellingNames);
const bestSellingCounts = @json($bestSellingCounts);

// Biểu đồ lợi nhuận theo ngày (Line Chart)
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: orderDates,
            datasets: [{
                label: 'Lợi Nhuận Theo Ngày',
                data: dailyProfits,
                borderColor: 'green',
                backgroundColor: 'rgba(0, 255, 0, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Lợi Nhuận (VND)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Ngày'
                    }
                }
            }
        }
    });
});

// Biểu đồ tồn kho (Bar Chart)
document.addEventListener("DOMContentLoaded", function () {
    if (productNames.length === 0 || stockCounts.length === 0) {
        console.warn("Không có dữ liệu tồn kho.");
        return;
    }

    const ctxStock = document.getElementById('stockChart').getContext('2d');
    new Chart(ctxStock, {
        type: 'bar',
        data: {
            labels: productNames,
            datasets: [{
                label: 'Số Lượng Tồn Kho',
                data: stockCounts,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Số Lượng'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Sản Phẩm'
                    }
                }
            }
        }
    });
});

// Biểu đồ trạng thái đơn hàng (Pie Chart)
document.addEventListener("DOMContentLoaded", function () {
    const ctxStatus = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: statusLabels,
            datasets: [{
                label: 'Trạng Thái Đơn Hàng',
                data: statusCounts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',  // Chờ xử lý - đỏ
                    'rgba(54, 162, 235, 0.6)',  // Đang xử lý - xanh dương
                    'rgba(255, 206, 86, 0.6)',  // Đang giao - vàng
                    'rgba(75, 192, 192, 0.6)',  // Hoàn thành - xanh lá
                    'rgba(153, 102, 255, 0.6)'  // Đã hủy - tím
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});

// Biểu đồ sản phẩm bán chạy (Bar Chart)
document.addEventListener("DOMContentLoaded", function () {
    const ctxBestSelling = document.getElementById('bestSellingChart').getContext('2d');
    new Chart(ctxBestSelling, {
        type: 'bar',
        data: {
            labels: bestSellingNames,
            datasets: [{
                label: 'Số Lượng Bán Ra',
                data: bestSellingCounts,
                backgroundColor: 'rgba(255, 159, 64, 0.6)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Số Lượng'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Sản Phẩm'
                    }
                }
            }
        }
    });
});
</script>

<style>
.card-container {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.card {
    background-color: #fff;
    border-radius: 8px;
    padding: 20px;
    flex: 1;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card h2 {
    font-size: 18px;
    margin: 0;
    color: #666;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.chart-container {
    margin-top: 30px;
}

canvas {
    width: 100% !important;
    height: auto !important;
}

.alert-box {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin: 20px 0;
    border-radius: 5px;
}
</style>
@endsection