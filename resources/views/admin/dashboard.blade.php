@extends('layouts.main')

@section('title', 'Dashboard')
@section('subTitle', 'Selamat datang di dashboard sistem kasir')

@section('content')
<div class="container-fluid">


    <!-- KPI Cards -->
    <div class="row" style="margin-bottom: 50px;">
        <!-- Total Penjualan Hari Ini -->
        <div class="col-md-3">
            <div class="card shadow-none border bg-gradient-start-1 h-100">
                <div class="card-body p-20">
                    <p class="fw-medium text-primary-light mb-3" style="font-size: 0.9rem;">💰 Penjualan Hari Ini</p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0" id="kpi-todays-sales" style="font-size: 1.5rem;">-</h6>
                        </div>
                        <div class="w-40-px h-40-px bg-blue rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="solar:wallet-bold" class="text-white text-xl mb-0"></iconify-icon>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center" style="gap: 1rem;">
                        <small class="text-muted" id="kpi-todays-transactions">-</small>
                        <div id="kpi-todays-sales-change" style="font-size: 0.875rem; text-align: right;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Penjualan Bulan Ini -->
        <div class="col-md-3">
            <div class="card shadow-none border bg-gradient-start-2 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">📅 Penjualan Bulan Ini</p>
                            <h6 class="mb-0" id="kpi-month-sales">-</h6>
                            <div class="mt-2" id="kpi-month-sales-change" style="font-size: 0.875rem;"></div>
                        </div>
                        <div class="w-50-px h-50-px bg-warning rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="solar:graph-bold" class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Penjualan Tahun Ini -->
        <div class="col-md-3">
            <div class="card shadow-none border bg-gradient-start-3 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">📆 Penjualan Tahun Ini</p>
                            <h6 class="mb-0" id="kpi-year-sales">-</h6>
                            <div class="mt-2" id="kpi-year-sales-change" style="font-size: 0.875rem;"></div>
                        </div>
                        <div class="w-50-px h-50-px bg-success rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="solar:chart-2-bold" class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Item Terjual Hari Ini -->
        <div class="col-md-3">
            <div class="card shadow-none border bg-gradient-start-4 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">🛍️ Item Hari Ini</p>
                            <h6 class="mb-0" id="kpi-todays-items">-</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-danger rounded-circle d-flex justify-content-center align-items-center">
                            <iconify-icon icon="solar:bag-2-bold" class="text-white text-2xl mb-0"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Details Row -->
    <div class="row g-4" style="margin-bottom: 50px;">
        <!-- Chart Penjualan -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">📈 Grafik Penjualan</h5>
                        <select id="chartRangeFilter" class="form-select form-select-sm" style="width: 150px;">
                            <option value="week">Minggu Terakhir</option>
                            <option value="1month" selected>Bulan Ini</option>
                            <option value="3months">3 Bulan Terakhir</option>
                            <option value="6months">6 Bulan Terakhir</option>
                        </select>
                    </div>
                    <div id="salesChart" class="apex-charts" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products & Payment Methods Row -->
    <div class="row g-4" style="margin-bottom: 50px;">
        <!-- Produk Terlaris -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 bg-transparent">
                    <h5 class="mb-0"><iconify-icon icon="solar:cup-star-bold" class="me-2"></iconify-icon>Produk Terlaris (All Time Top 5)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th class="text-end">Terjual</th>
                            </tr>
                        </thead>
                        <tbody id="top-products-container">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Memuat...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 bg-transparent">
                    <h5 class="mb-0"><iconify-icon icon="solar:card-2-bold" class="me-2"></iconify-icon>Metode Pembayaran Hari Ini</h5>
                </div>
                <div class="card-body" id="payment-methods-container">
                    <div class="text-center text-muted py-4">Memuat...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Warning & Category Stats -->
    <div class="row g-4" style="margin-bottom: 50px;">
        <!-- Stok Warning -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 bg-transparent">
                    <h5 class="mb-0"><iconify-icon icon="solar:danger-triangle-bold" class="me-2"></iconify-icon>Produk Stok Terbatas (&lt; 5)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Stok</th>
                            </tr>
                        </thead>
                        <tbody id="low-stock-container">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Memuat...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Category Stats -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 bg-transparent">
                    <h5 class="mb-0"><iconify-icon icon="solar:widget-add-bold" class="me-2"></iconify-icon>Penjualan per Kategori (Hari Ini)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th class="text-center">Item</th>
                                <th class="text-center">Transaksi</th>
                            </tr>
                        </thead>
                        <tbody id="category-stats-container">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Memuat...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    let salesChart = null;

    // Helper function untuk ambil warna
    function getChartColorsArray(chartId) {
        if (document.getElementById(chartId) !== null) {
            var colors = document.getElementById(chartId).getAttribute("data-colors");
            if (colors) {
                colors = JSON.parse(colors);
                return colors.map(function(value) {
                    var newValue = value.replace(" ", "");
                    if (newValue.indexOf(",") === -1) {
                        var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                        if (color) return color;
                        else return newValue;
                    } else {
                        var val = value.split(',');
                        if (val.length == 2) {
                            var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                            rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                            return rgbaColor;
                        } else {
                            return newValue;
                        }
                    }
                });
            }
        }
    }

    // Load seluruh data dashboard via satu request
    function loadDashboard() {
        fetch('{{ route("dashboard.api.summary") }}')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                const k = data.kpi;
                document.getElementById('kpi-todays-sales').textContent = k.todays_sales.formatted;
                updateKPIChange('kpi-todays-sales-change', k.todays_sales.change_percent, k.todays_sales.is_increase, 'dari kemarin');
                document.getElementById('kpi-todays-transactions').textContent = k.todays_transactions + ' transaksi';
                document.getElementById('kpi-month-sales').textContent = k.month_sales.formatted;
                updateKPIChange('kpi-month-sales-change', k.month_sales.change_percent, k.month_sales.is_increase, 'vs bulan lalu');
                document.getElementById('kpi-year-sales').textContent = k.year_sales.formatted;
                updateKPIChange('kpi-year-sales-change', k.year_sales.change_percent, k.year_sales.is_increase, 'vs tahun lalu');
                document.getElementById('kpi-todays-items').textContent = k.todays_items;

                // Tampilkan tiap panel berurutan (stagger) biar tetap terasa "per element"
                renderPaymentMethods(data.payment_methods, data.todays_sales);
                setTimeout(() => renderTopProducts(data.top_products), 120);
                setTimeout(() => renderLowStockProducts(data.low_stock), 240);
                setTimeout(() => renderCategoryStats(data.category_stats), 360);
            });
    }

    function updateKPIChange(elementId, changePercent, isIncrease, periodLabel) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const color = isIncrease ? '#198754' : '#dc3545';
        element.innerHTML = `<span style="color: ${color}; font-size: 0.875rem; font-weight: 600;">${Math.abs(changePercent)}% ${periodLabel}</span>`;
    }

    // Load Sales Chart (ApexCharts)
    function loadSalesChart(range = '1month') {
        fetch('{{ route("dashboard.api.chart.sales") }}?range=' + range)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderSalesChart(data.data);
                }
            });
    }

    function renderSalesChart(chartData) {
        var options = {
            series: [{
                name: 'Penjualan (Rp)',
                data: chartData.sales,
                type: 'column'
            }],
            chart: {
                height: 350,
                type: 'bar',
                stacked: false,
                toolbar: {
                    show: true
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    columnWidth: '55%',
                    borderRadius: 8,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            colors: ['#0d6efd'],
            fill: {
                opacity: 0.85,
                gradient: {
                    inverseColors: false,
                    shade: 'light',
                    type: "vertical",
                    opacityFrom: 0.85,
                    opacityTo: 0.55,
                    stops: [0, 100, 100, 100]
                }
            },
            labels: chartData.dates,
            markers: {
                size: 0
            },
            xaxis: {
                type: 'category',
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Penjualan (Rp)',
                    style: {
                        color: '#0d6efd',
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    formatter: function(val) {
                        return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                    },
                    style: {
                        colors: '#0d6efd'
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                    const sales = series[0][dataPointIndex];
                    const label = w.globals.labels[dataPointIndex];

                    return '<div style="padding: 12px; background: white; border: 1px solid #e3e3e3; border-radius: 6px; min-width: 200px;">' +
                        '<div style="margin-bottom: 8px;"><strong style="font-size: 13px;">' + label + '</strong></div>' +
                        '<div style="display: flex; align-items: center;">' +
                        '<span style="width: 10px; height: 10px; background: #0d6efd; border-radius: 2px; margin-right: 8px;"></span>' +
                        '<span style="color: #6c757d; font-size: 12px;">Penjualan:</span>' +
                        '<span style="margin-left: auto; font-weight: 600; font-size: 12px;">Rp ' + (sales).toLocaleString('id-ID') + '</span>' +
                        '</div>' +
                        '</div>';
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                offsetY: 0,
                markers: {
                    fillColors: ['#0d6efd']
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 8
                }
            },
            grid: {
                borderColor: '#f1f1f1'
            }
        };

        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new ApexCharts(document.querySelector("#salesChart"), options);
        salesChart.render();
    }

    function renderPaymentMethods(methods, total) {
        let html = '';
        if (methods.length === 0) {
            html = `
                <div class="text-center py-5">
                    <iconify-icon icon="solar:box-minimalistic-line-duotone" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></iconify-icon>
                    <p class="text-muted small">Belum ada transaksi hari ini</p>
                </div>
            `;
        } else {
            methods.forEach(method => {
                const percentage = total > 0 ? Math.round((method.total / total) * 100) : 0;
                let methodName = '';
                let barColor = '';
                
                if (method.payment_method === 'cash') {
                    methodName = '💵 Tunai';
                    barColor = '#198754';
                } else if (method.payment_method === 'qris') {
                    methodName = '📱 QRIS';
                    barColor = '#0d6efd';
                } else {
                    methodName = '🏦 Transfer';
                    barColor = '#fd7e14';
                }
                
                html += `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-medium">${methodName}</span>
                            <span class="badge bg-light text-dark">${percentage}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: ${percentage}%; background-color: ${barColor};"></div>
                        </div>
                        <small class="text-muted d-block mt-1">Rp ${(method.total).toLocaleString('id-ID')} (${method.count}x)</small>
                    </div>
                `;
            });
        }
        document.getElementById('payment-methods-container').innerHTML = html;
    }

    function renderTopProducts(products) {
        let html = '';
        if (products.length === 0) {
            html = `
                <tr>
                    <td colspan="3">
                        <div class="text-center py-5">
                            <iconify-icon icon="solar:box-minimalistic-line-duotone" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></iconify-icon>
                            <p class="text-muted small">Belum ada data penjualan</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            products.forEach((product, key) => {
                let badge = '';
                if (key === 0) {
                    badge = '<span class="badge bg-warning-subtle text-warning fw-bold">#1</span>';
                } else if (key === 1) {
                    badge = '<span class="badge bg-secondary-subtle text-secondary fw-bold">#2</span>';
                } else if (key === 2) {
                    badge = '<span class="badge bg-danger-subtle text-danger fw-bold">#3</span>';
                } else {
                    badge = `<span class="badge bg-light text-dark fw-bold">#${key + 1}</span>`;
                }

                html += `
                    <tr>
                        <td>${badge}</td>
                        <td><strong>${product.product_name}</strong></td>
                        <td class="text-end"><strong>${product.total_qty} pcs</strong></td>
                    </tr>
                `;
            });
        }
        document.getElementById('top-products-container').innerHTML = html;
    }



    function renderLowStockProducts(products) {
        let html = '';
        if (products.length === 0) {
            html = `
                <tr>
                    <td colspan="3">
                        <div class="text-center py-5">
                            <iconify-icon icon="solar:check-circle-line-duotone" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></iconify-icon>
                            <p class="text-muted small">✓ Semua stok dalam kondisi baik</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            products.forEach(product => {
                let stockBadge = '';
                if (product.stock === 0) {
                    stockBadge = '<span class="badge bg-danger-subtle text-danger fw-bold">HABIS</span>';
                } else if (product.stock <= 2) {
                    stockBadge = `<span class="badge bg-warning-subtle text-warning fw-bold">${product.stock} ${product.unit}</span>`;
                } else {
                    stockBadge = `<span class="badge bg-info-subtle text-info fw-bold">${product.stock} ${product.unit}</span>`;
                }

                html += `
                    <tr>
                        <td><strong>${product.name}</strong></td>
                        <td><span class="badge bg-light text-dark">${product.category?.name || 'N/A'}</span></td>
                        <td class="text-end">${stockBadge}</td>
                    </tr>
                `;
            });
        }
        document.getElementById('low-stock-container').innerHTML = html;
    }

    function renderCategoryStats(categories) {
        let html = '';
        if (categories.length === 0) {
            html = `
                <tr>
                    <td colspan="3">
                        <div class="text-center py-5">
                            <iconify-icon icon="solar:box-minimalistic-line-duotone" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></iconify-icon>
                            <p class="text-muted small">Belum ada data penjualan</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            const totalQty = categories.reduce((sum, cat) => sum + cat.total_qty, 0);
            const totalCount = categories.reduce((sum, cat) => sum + cat.count, 0);
            
            categories.forEach(category => {
                const qtyPercentage = totalQty > 0 ? Math.round((category.total_qty / totalQty) * 100) : 0;
                
                html += `
                    <tr>
                        <td>
                            <strong>${category.name}</strong>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: ${qtyPercentage}%"></div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-info-subtle text-info">${category.total_qty}</span></td>
                        <td class="text-center"><span class="badge bg-success-subtle text-success">${category.count}</span></td>
                    </tr>
                `;
            });
        }
        document.getElementById('category-stats-container').innerHTML = html;
    }

    // Initialize Dashboard
    function initDashboard() {
        loadDashboard();
        loadSalesChart();
    }

    // Load on page ready
    document.addEventListener('DOMContentLoaded', function() {
        initDashboard();
        
        // Handle sales chart filter
        document.getElementById('chartRangeFilter').addEventListener('change', function() {
            loadSalesChart(this.value);
        });
    });

    // Auto refresh every 5 minutes
    setInterval(initDashboard, 5 * 60 * 1000);
</script>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .bg-gradient-start-1 {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .bg-gradient-start-2 {
        background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%);
        animation: fadeInUp 0.6s ease-out 0.1s forwards;
    }

    .bg-gradient-start-3 {
        background: linear-gradient(135deg, #e0f2f1 0%, #f1f8e9 100%);
        animation: fadeInUp 0.6s ease-out 0.2s forwards;
    }

    .bg-gradient-start-4 {
        background: linear-gradient(135deg, #ffe0b2 0%, #ffebee 100%);
        animation: fadeInUp 0.6s ease-out 0.3s forwards;
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        animation: slideInLeft 0.5s ease-out;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .card-header {
        padding: 1.25rem;
        border-bottom: none;
    }

    .card-header.bg-transparent {
        background-color: transparent !important;
    }

    .card-header.border-0 {
        border: none !important;
    }

    .card-header h5 {
        display: flex;
        align-items: center;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1rem;
    }

    .card-header iconify-icon {
        font-size: 1.3rem;
        color: #0d6efd;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .w-50-px {
        width: 50px;
    }

    .h-50-px {
        height: 50px;
    }

    .w-40-px {
        width: 40px;
    }

    .h-40-px {
        height: 40px;
    }

    .p-20 {
        padding: 1.25rem;
    }

    .text-primary-light {
        color: #64748B;
    }

    .bg-blue {
        background-color: #0d6efd;
    }

    .bg-warning {
        background-color: #ffc107;
    }

    .bg-success {
        background-color: #198754;
    }

    .bg-danger {
        background-color: #dc3545;
    }

    /* Badge animations */
    .badge {
        animation: fadeInUp 0.4s ease-out;
    }

    /* Progress bar animation */
    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.6s ease-in-out;
        background: linear-gradient(90deg, #0d6efd 0%, #0d6efd 100%);
    }

    /* Badge subtle variants */
    .badge.bg-warning-subtle {
        background-color: #fff3cd !important;
    }

    .badge.bg-danger-subtle {
        background-color: #f8d7da !important;
    }

    .badge.bg-info-subtle {
        background-color: #d1ecf1 !important;
    }

    .badge.bg-success-subtle {
        background-color: #d4edda !important;
    }

    .badge.bg-secondary-subtle {
        background-color: #e2e3e5 !important;
    }

    /* Avatar styling */
    .avatar-sm {
        min-width: 32px;
        min-height: 32px;
    }

    /* Select animation */
    select {
        transition: all 0.3s ease;
    }

    select:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    /* ApexCharts styling */
    .apex-charts {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .apexcharts-tooltip {
        background: white !important;
        border: 1px solid #e9ecef !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .apexcharts-tooltip-custom {
        padding: 12px;
        background: white;
        border: 1px solid #e3e3e3;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
