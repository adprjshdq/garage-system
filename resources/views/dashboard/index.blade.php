@extends('layouts.app')

@section('title', 'Dashboard Operasional Bengkel')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--mc-primary);">Dashboard Operasional</h3>
        <p class="text-muted small mb-0">Ringkasan aktivitas servis, pendapatan, inventaris, dan piutang bengkel motor.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('receptions.create') }}" class="btn btn-mc-accent shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Terima Kendaraan Baru
        </a>
        <a href="{{ route('work-orders.create') }}" class="btn btn-mc-primary shadow-sm">
            <i class="bi bi-file-earmark-plus me-1"></i> Buat Work Order
        </a>
    </div>
</div>

<!-- 1. Statistik Cards -->
<div class="row g-3 mb-4">
    <!-- Servis Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Servis Masuk Hari Ini</span>
                <h3 class="fw-bold my-1 text-dark">{{ $todayServicesCount }}</h3>
                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> Per hari ini</small>
            </div>
            <div class="icon-box bg-primary-subtle text-primary">
                <i class="bi bi-tools"></i>
            </div>
        </div>
    </div>

    <!-- Sedang Dikerjakan -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Sedang Dikerjakan</span>
                <h3 class="fw-bold my-1 text-primary">{{ $inProgressCount }}</h3>
                <small class="text-muted"><i class="bi bi-wrench me-1"></i> Di area pit mekanik</small>
            </div>
            <div class="icon-box bg-info-subtle text-info">
                <i class="bi bi-gear-wide"></i>
            </div>
        </div>
    </div>

    <!-- Servis Selesai Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Servis Selesai Hari Ini</span>
                <h3 class="fw-bold my-1 text-success">{{ $completedTodayCount }}</h3>
                <small class="text-success"><i class="bi bi-check2-circle me-1"></i> Siap diambil/ditagih</small>
            </div>
            <div class="icon-box bg-success-subtle text-success">
                <i class="bi bi-check2-all"></i>
            </div>
        </div>
    </div>

    <!-- Kas Masuk Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Uang Kas Masuk Hari Ini</span>
                <h3 class="fw-bold my-1" style="color: var(--mc-accent);">Rp{{ number_format($actualPaymentsToday, 0, ',', '.') }}</h3>
                <small class="text-muted">Tagihan: Rp{{ number_format($invoicedToday, 0, ',', '.') }}</small>
            </div>
            <div class="icon-box bg-warning-subtle text-warning">
                <i class="bi bi-cash-coin"></i>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Sekunder: Piutang & Stok Rendah -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-custom p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Piutang Pelanggan (Belum Lunas)</span>
                    <h4 class="fw-bold text-danger my-1">Rp{{ number_format($totalOutstandingReceivable, 0, ',', '.') }}</h4>
                    <small class="text-muted">Dari {{ $unpaidInvoicesCount }} invoice jatuh tempo / belum lunas</small>
                </div>
                <a href="{{ route('payments.receivables') }}" class="btn btn-sm btn-outline-danger">
                    Lihat Piutang <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Peringatan Stok Spare Part Menipis</span>
                    <h4 class="fw-bold text-warning my-1">{{ $lowStockCount }} Item</h4>
                    <small class="text-muted">Jumlah stok fisik mencapai atau di bawah batas minimum</small>
                </div>
                <a href="{{ route('spare-parts.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-outline-warning text-dark">
                    Cek Stok Gudang <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 2. Charts Section -->
<div class="row g-3 mb-4">
    <!-- Tren Pendapatan 7 Hari Terakhir -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Tren Finansial 7 Hari Terakhir</h5>
                <span class="badge bg-light text-secondary border">Tagihan vs Kas Aktual</span>
            </div>
            <div style="height: 270px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Work Order -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold text-dark mb-3">Distribusi Status Work Order</h5>
            <div style="height: 220px;" class="d-flex justify-content-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-3 small text-center text-muted">
                Total data operasional Work Order tersimpan di sistem
            </div>
        </div>
    </div>
</div>

<!-- 3. Tabel Data Terkini -->
<div class="row g-3 mb-4">
    <!-- Work Order Terbaru -->
    <div class="col-lg-7">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-1 text-primary"></i> Work Order Terbaru</h5>
                <a href="{{ route('work-orders.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. WO</th>
                            <th>Pelanggan / Motor</th>
                            <th>Status</th>
                            <th>Estimasi Biaya</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWorkOrders as $wo)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $wo->wo_number }}</span>
                                    <div class="text-muted small">{{ $wo->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $wo->customer->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $wo->vehicle->license_plate ?? '-' }} &bull; {{ $wo->vehicle->model ?? '' }}</small>
                                </td>
                                <td>{!! $wo->status_badge !!}</td>
                                <td>Rp{{ number_format($wo->actual_total_cost > 0 ? $wo->actual_total_cost : $wo->estimated_cost, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada Work Order yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kendaraan Sedang Dikerjakan -->
    <div class="col-lg-5">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-tools me-1 text-info"></i> Sedang Dikerjakan</h5>
                <span class="badge bg-info-subtle text-info border">{{ $activeWorkOrders->count() }} Motor</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Motor</th>
                            <th>Keluhan</th>
                            <th>Mekanik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeWorkOrders as $awo)
                            <tr>
                                <td>
                                    <span class="badge bg-dark">{{ $awo->vehicle->license_plate ?? '-' }}</span>
                                    <div class="small fw-semibold mt-1">{{ $awo->vehicle->model ?? '' }}</div>
                                </td>
                                <td>
                                    <small class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $awo->customer_complaint }}">
                                        {{ $awo->customer_complaint }}
                                    </small>
                                </td>
                                <td>
                                    @foreach($awo->mechanics as $mech)
                                        <span class="badge bg-light text-dark border">{{ $mech->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Tidak ada kendaraan yang sedang dalam proses pengerjaan saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 4. Baris Ketiga: Invoice Terbaru & Log Audit -->
<div class="row g-3">
    <!-- Invoice Terbaru -->
    <div class="col-lg-6">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-receipt me-1 text-success"></i> Invoice Terbaru</h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="fw-semibold text-decoration-none">
                                        {{ $inv->invoice_number }}
                                    </a>
                                    <div class="text-muted small">{{ $inv->invoice_date->format('d/m/Y') }}</div>
                                </td>
                                <td>{{ $inv->customer->name ?? '-' }}</td>
                                <td>
                                    <div class="fw-bold">Rp{{ number_format($inv->grand_total, 0, ',', '.') }}</div>
                                    @if($inv->balance_due > 0)
                                        <small class="text-danger">Sisa: Rp{{ number_format($inv->balance_due, 0, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td>{!! $inv->status_badge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada invoice yang diterbitkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aktivitas Terbaru (Audit Log) -->
    <div class="col-lg-6">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-activity me-1 text-warning"></i> Aktivitas Sistem Terbaru</h5>
                @if(auth()->user()->isOwner())
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-link text-decoration-none">Semua Audit</a>
                @endif
            </div>
            <div class="list-group list-group-flush small">
                @forelse($recentAudits as $audit)
                    <div class="list-group-item px-0 py-2 border-bottom d-flex align-items-start gap-2">
                        <span class="badge bg-secondary-subtle text-dark border mt-1">{{ $audit->module }}</span>
                        <div class="flex-grow-1">
                            <div>{{ $audit->summary }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                Oleh: <strong>{{ $audit->user->name ?? 'Sistem' }}</strong> &bull; {{ $audit->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">Belum ada aktivitas tercatat.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="application/json" id="revenueData">
    {!! json_encode($last7Days) !!}
</script>
<script type="application/json" id="statusData">
    {!! json_encode($woStatusCounts) !!}
</script>
<script>
    // 1. Tren Pendapatan Chart
    const ctxRevenue = document.getElementById('revenueChart');
    if (ctxRevenue) {
        const last7DaysData = JSON.parse(document.getElementById('revenueData').textContent || '[]');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: last7DaysData.map(d => d.date),
                datasets: [
                    {
                        label: 'Tagihan Diterbitkan (Invoiced)',
                        data: last7DaysData.map(d => d.invoiced),
                        borderColor: '#1e3a8a',
                        backgroundColor: 'rgba(30, 58, 138, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 4,
                    },
                    {
                        label: 'Uang Kas Diterima (Actual Paid)',
                        data: last7DaysData.map(d => d.paid),
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Status Doughnut Chart
    const ctxStatus = document.getElementById('statusChart');
    if (ctxStatus) {
        const statusData = JSON.parse(document.getElementById('statusData').textContent || '{}');
        const labels = Object.keys(statusData);
        const dataValues = Object.values(statusData);

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: labels.map(l => l.replace('_', ' ').toUpperCase()),
                datasets: [{
                    data: dataValues,
                    backgroundColor: [
                        '#64748b', '#f59e0b', '#06b6d4', '#3b82f6', '#ec4899', '#8b5cf6', '#10b981', '#14b8a6', '#1e293b', '#ef4444'
                    ],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                }
            }
        });
    }
</script>
@endpush

