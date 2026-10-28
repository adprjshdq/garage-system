<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Mechanic;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Role;
use App\Models\ServiceCatalog;
use App\Models\ServiceReception;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\WorkOrderApproval;
use App\Models\WorkOrderInspection;
use App\Models\WorkOrderItem;
use App\Services\DocumentNumberService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([DatabaseSeeder::class]);
        $docService = app(DocumentNumberService::class);

        // 1. Roles
        $ownerRole = Role::where('name', 'owner')->first();
        $cashierRole = Role::where('name', 'admin_cashier')->first();
        $saRole = Role::where('name', 'service_advisor')->first();
        $mechanicRole = Role::where('name', 'mechanic')->first();
        $warehouseRole = Role::where('name', 'warehouse')->first();

        // 2. Demo Users for all roles
        $users = [
            'owner' => User::firstOrCreate(
                ['email' => 'owner@motocare.com'],
                ['name' => 'Budi Santoso', 'username' => 'owner', 'password' => Hash::make('password123'), 'phone' => '081211112222', 'is_active' => true]
            ),
            'cashier' => User::firstOrCreate(
                ['email' => 'kasir@motocare.com'],
                ['name' => 'Siti Rahmawati', 'username' => 'kasir', 'password' => Hash::make('password123'), 'phone' => '081233334444', 'is_active' => true]
            ),
            'sa' => User::firstOrCreate(
                ['email' => 'sa@motocare.com'],
                ['name' => 'Rendra Pratama', 'username' => 'service_advisor', 'password' => Hash::make('password123'), 'phone' => '081255556666', 'is_active' => true]
            ),
            'mechanic' => User::firstOrCreate(
                ['email' => 'mekanik@motocare.com'],
                ['name' => 'Agus Gunawan', 'username' => 'mekanik', 'password' => Hash::make('password123'), 'phone' => '081277778888', 'is_active' => true]
            ),
            'warehouse' => User::firstOrCreate(
                ['email' => 'gudang@motocare.com'],
                ['name' => 'Hendra Wijaya', 'username' => 'gudang', 'password' => Hash::make('password123'), 'phone' => '081299990000', 'is_active' => true]
            ),
        ];

        $users['owner']->roles()->sync([$ownerRole->id]);
        $users['cashier']->roles()->sync([$cashierRole->id]);
        $users['sa']->roles()->sync([$saRole->id]);
        $users['mechanic']->roles()->sync([$mechanicRole->id]);
        $users['warehouse']->roles()->sync([$warehouseRole->id]);

        // 3. Mechanics
        $mech1 = Mechanic::firstOrCreate(['code' => 'MECH-0001'], [
            'user_id' => $users['mechanic']->id,
            'name' => 'Agus Gunawan',
            'phone' => '081277778888',
            'specialization' => 'CVT & Mesin Matic',
            'is_active' => true,
        ]);

        $mech2 = Mechanic::firstOrCreate(['code' => 'MECH-0002'], [
            'name' => 'Doni Irawan',
            'phone' => '081388889999',
            'specialization' => 'Kelistrikan & Injeksi',
            'is_active' => true,
        ]);

        $mech3 = Mechanic::firstOrCreate(['code' => 'MECH-0003'], [
            'name' => 'Joko Susilo',
            'phone' => '081499991111',
            'specialization' => 'Suspensi & Pengereman',
            'is_active' => true,
        ]);

        // 4. Service Catalog
        $services = [
            ['code' => 'SRV-001', 'name' => 'Servis Ringan / Rutin', 'category' => 'Servis Berkala', 'description' => 'Pembersihan filter udara, cek busi, setel rantai/CVT, cek rem depan-belakang', 'price' => 60000, 'estimated_duration_minutes' => 45],
            ['code' => 'SRV-002', 'name' => 'Servis CVT Matic Lengkap', 'category' => 'Transmisi CVT', 'description' => 'Bongkar dan bersihkan pulley depan-belakang, ganti gemuk CVT, pembersihan mangkok kopling', 'price' => 75000, 'estimated_duration_minutes' => 60],
            ['code' => 'SRV-003', 'name' => 'Jasa Ganti Oli Mesin & Gardan', 'category' => 'Pelumasan', 'description' => 'Penggantian oli mesin dan oli transmisi/gardan matic', 'price' => 15000, 'estimated_duration_minutes' => 15],
            ['code' => 'SRV-004', 'name' => 'Tune Up & Gurah Injeksi (Throttle Body)', 'category' => 'Sistem Bahan Bakar', 'description' => 'Pembersihan injector dan throttle body dengan ultrasonic cleaner', 'price' => 95000, 'estimated_duration_minutes' => 60],
            ['code' => 'SRV-005', 'name' => 'Jasa Ganti Kampas Rem Depan / Belakang', 'category' => 'Pengereman', 'description' => 'Penggantian kampas rem cakram atau tromol dan bleeding minyak rem', 'price' => 25000, 'estimated_duration_minutes' => 30],
            ['code' => 'SRV-006', 'name' => 'Overhaul Mesin (Turun Mesin)', 'category' => 'Mesin Berat', 'description' => 'Bongkar mesin total, ganti seher/piston, skir klep, ganti paking set', 'price' => 450000, 'estimated_duration_minutes' => 360],
        ];

        foreach ($services as $srv) {
            ServiceCatalog::firstOrCreate(['code' => $srv['code']], $srv);
        }

        // 5. Spare Part Categories
        $categories = [
            'OLI' => SparePartCategory::firstOrCreate(['code' => 'CAT-OLI'], ['name' => 'Oli & Pelumas', 'description' => 'Oli mesin, oli gardan, dan minyak rem']),
            'REM' => SparePartCategory::firstOrCreate(['code' => 'CAT-REM'], ['name' => 'Sistem Pengereman', 'description' => 'Kampas rem depan, belakang, dan piringan cakram']),
            'CVT' => SparePartCategory::firstOrCreate(['code' => 'CAT-CVT'], ['name' => 'Transmisi & CVT', 'description' => 'V-Belt, roller, kampas ganda, dan sliding roller']),
            'LIS' => SparePartCategory::firstOrCreate(['code' => 'CAT-LIS'], ['name' => 'Kelistrikan & Pengapian', 'description' => 'Busi, aki, bohlam lampu, dan sekring']),
            'FLT' => SparePartCategory::firstOrCreate(['code' => 'CAT-FLT'], ['name' => 'Filter & Pendingin', 'description' => 'Filter udara dan air radiator coolant']),
        ];

        // 6. Spare Parts
        $partsData = [
            ['category_id' => $categories['OLI']->id, 'sku' => 'PRT-OLI-001', 'name' => 'Oli Mesin MPX2 Matic 0.8L', 'unit' => 'Botol', 'reference_cost_price' => 45000, 'selling_price' => 58000, 'stock_quantity' => 25, 'min_stock' => 5, 'rack_location' => 'Rak A-1'],
            ['category_id' => $categories['OLI']->id, 'sku' => 'PRT-OLI-002', 'name' => 'Oli Gardan Matic 120ml', 'unit' => 'Botol', 'reference_cost_price' => 12000, 'selling_price' => 18000, 'stock_quantity' => 30, 'min_stock' => 8, 'rack_location' => 'Rak A-2'],
            ['category_id' => $categories['REM']->id, 'sku' => 'PRT-REM-001', 'name' => 'Kampas Rem Depan Vario/Beat', 'unit' => 'Set', 'reference_cost_price' => 35000, 'selling_price' => 50000, 'stock_quantity' => 12, 'min_stock' => 4, 'rack_location' => 'Rak B-1'],
            ['category_id' => $categories['REM']->id, 'sku' => 'PRT-REM-002', 'name' => 'Kampas Rem Belakang Tromol Matic', 'unit' => 'Set', 'reference_cost_price' => 38000, 'selling_price' => 55000, 'stock_quantity' => 8, 'min_stock' => 3, 'rack_location' => 'Rak B-2'],
            ['category_id' => $categories['CVT']->id, 'sku' => 'PRT-CVT-001', 'name' => 'V-Belt Kit Honda Vario 150/160', 'unit' => 'Set', 'reference_cost_price' => 135000, 'selling_price' => 185000, 'stock_quantity' => 6, 'min_stock' => 2, 'rack_location' => 'Rak C-1'],
            ['category_id' => $categories['CVT']->id, 'sku' => 'PRT-CVT-002', 'name' => 'Roller Set Standar Beat FI', 'unit' => 'Set', 'reference_cost_price' => 32000, 'selling_price' => 48000, 'stock_quantity' => 10, 'min_stock' => 3, 'rack_location' => 'Rak C-2'],
            ['category_id' => $categories['LIS']->id, 'sku' => 'PRT-LIS-001', 'name' => 'Busi NGK CPR9EA-9 (Standar Matic)', 'unit' => 'Pcs', 'reference_cost_price' => 16000, 'selling_price' => 25000, 'stock_quantity' => 20, 'min_stock' => 5, 'rack_location' => 'Rak D-1'],
            ['category_id' => $categories['LIS']->id, 'sku' => 'PRT-LIS-002', 'name' => 'Aki Kering GS Astra GTZ5S', 'unit' => 'Unit', 'reference_cost_price' => 210000, 'selling_price' => 275000, 'stock_quantity' => 2, 'min_stock' => 3, 'rack_location' => 'Rak D-2'], // Low stock trigger!
            ['category_id' => $categories['FLT']->id, 'sku' => 'PRT-FLT-001', 'name' => 'Filter Udara Vario 160 / PCX 160', 'unit' => 'Pcs', 'reference_cost_price' => 42000, 'selling_price' => 62000, 'stock_quantity' => 7, 'min_stock' => 3, 'rack_location' => 'Rak E-1'],
            ['category_id' => $categories['FLT']->id, 'sku' => 'PRT-FLT-002', 'name' => 'Air Radiator Coolant 1L', 'unit' => 'Botol', 'reference_cost_price' => 22000, 'selling_price' => 32000, 'stock_quantity' => 15, 'min_stock' => 4, 'rack_location' => 'Rak E-2'],
        ];

        $parts = [];
        foreach ($partsData as $pd) {
            $part = SparePart::firstOrCreate(['sku' => $pd['sku']], $pd);
            $parts[$pd['sku']] = $part;

            // Seed initial stock movement if empty
            if ($part->stockMovements()->count() === 0) {
                StockMovement::create([
                    'spare_part_id' => $part->id,
                    'user_id' => $users['warehouse']->id,
                    'movement_type' => 'initial',
                    'quantity' => $part->stock_quantity,
                    'stock_before' => 0,
                    'stock_after' => $part->stock_quantity,
                    'unit_price' => $part->reference_cost_price,
                    'reference_type' => 'initial_setup',
                    'notes' => 'Saldo awal stok gudang',
                ]);
            }
        }

        // Trigger low stock notification for Aki
        Notification::create([
            'target_role' => 'warehouse',
            'type' => 'low_stock',
            'title' => 'Peringatan Stok Menipis!',
            'message' => "Stok untuk spare part 'Aki Kering GS Astra GTZ5S' tersisa 2 Unit (Batas minimum: 3 Unit).",
            'url' => route('spare-parts.show', $parts['PRT-LIS-002']->id),
            'is_read' => false,
            'created_at' => now(),
        ]);

        // 7. Suppliers
        $sup1 = Supplier::firstOrCreate(['code' => 'SUPP-0001'], [
            'name' => 'PT Astra Honda Genuine Partsindo',
            'phone' => '021-65301234',
            'email' => 'sales@ahg-partsindo.co.id',
            'address' => 'Kawasan Industri Pulogadung Blok B No. 12, Jakarta Timur',
            'is_active' => true,
        ]);

        $sup2 = Supplier::firstOrCreate(['code' => 'SUPP-0002'], [
            'name' => 'CV Sumber Makmur Sparepart',
            'phone' => '021-54328765',
            'email' => 'distro@sumbermakmur.com',
            'address' => 'Jl. Kebon Jeruk No. 45, Jakarta Barat',
            'is_active' => true,
        ]);

        // 8. Purchase Order with partial goods receipt
        $poNumber = 'PO/'.date('Ym').'/0001';
        $po = PurchaseOrder::firstOrCreate(['po_number' => $poNumber], [
            'supplier_id' => $sup1->id,
            'created_by' => $users['warehouse']->id,
            'approved_by' => $users['owner']->id,
            'order_date' => now()->subDays(5)->toDateString(),
            'expected_date' => now()->subDays(2)->toDateString(),
            'subtotal' => 1125000,
            'tax_amount' => 0,
            'grand_total' => 1125000,
            'status' => 'partially_received',
            'notes' => 'Pengadaan rutin oli dan kampas rem',
        ]);

        $poItem1 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po->id, 'spare_part_id' => $parts['PRT-OLI-001']->id],
            ['quantity_ordered' => 20, 'quantity_received' => 10, 'unit_cost' => 45000, 'subtotal' => 900000]
        );
        $poItem2 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po->id, 'spare_part_id' => $parts['PRT-REM-001']->id],
            ['quantity_ordered' => 5, 'quantity_received' => 5, 'unit_cost' => 35000, 'subtotal' => 225000]
        );

        // Goods Receipt 1
        $grn = GoodsReceipt::firstOrCreate(['grn_number' => 'GRN/'.date('Ym').'/0001'], [
            'purchase_order_id' => $po->id,
            'received_by' => $users['warehouse']->id,
            'receipt_date' => now()->subDays(2)->toDateString(),
            'notes' => 'Penerimaan tahap 1 (10 botol oli + 5 set kampas rem)',
        ]);

        // 9. Expense Categories & Expenses
        $expCatListrik = ExpenseCategory::firstOrCreate(['name' => 'Listrik & Air Bengkel'], ['description' => 'Tagihan PLN dan PDAM bulanan']);
        $expCatAlat = ExpenseCategory::firstOrCreate(['name' => 'Perawatan Peralatan'], ['description' => 'Kompresor, impact wrench, kunci-kunci']);
        $expCatKonsumsi = ExpenseCategory::firstOrCreate(['name' => 'Konsumsi Karyawan'], ['description' => 'Kopi, teh, dan makan siang operasional']);

        Expense::firstOrCreate(['expense_number' => 'EXP/'.date('Ym').'/0001'], [
            'expense_category_id' => $expCatListrik->id,
            'recorded_by' => $users['cashier']->id,
            'expense_date' => now()->subDays(3)->toDateString(),
            'amount' => 450000,
            'payment_method' => 'bank_transfer',
            'description' => 'Pembayaran tagihan listrik PLN bengkel periode bulan ini',
            'status' => 'approved',
            'approved_by' => $users['owner']->id,
        ]);

        Expense::firstOrCreate(['expense_number' => 'EXP/'.date('Ym').'/0002'], [
            'expense_category_id' => $expCatKonsumsi->id,
            'recorded_by' => $users['cashier']->id,
            'expense_date' => now()->subDay()->toDateString(),
            'amount' => 85000,
            'payment_method' => 'cash',
            'description' => 'Pembelian galon air mineral & kopi sachet mekanik',
            'status' => 'approved',
            'approved_by' => $users['owner']->id,
        ]);

        // 10. Customers & Vehicles
        $cust1 = Customer::firstOrCreate(['customer_code' => 'CUST-0001'], [
            'name' => 'Bambang Supriyanto',
            'phone' => '081288881234',
            'email' => 'bambang.supri@gmail.com',
            'address' => 'Jl. Melati Indah No. 15, Cilandak, Jaksel',
            'is_active' => true,
        ]);

        $veh1 = Vehicle::firstOrCreate(['license_plate' => 'B 3456 SKA'], [
            'customer_id' => $cust1->id,
            'brand' => 'Honda',
            'model' => 'Vario 160 CBS',
            'year' => 2023,
            'color' => 'Hitam Matte',
            'last_mileage' => 14250,
            'is_active' => true,
        ]);

        $cust2 = Customer::firstOrCreate(['customer_code' => 'CUST-0002'], [
            'name' => 'Dewi Anggraini',
            'phone' => '085712345678',
            'email' => 'dewi.ang@yahoo.com',
            'address' => 'Pondok Indah Townhouse Blok C-3, Jaksel',
            'is_active' => true,
        ]);

        $veh2 = Vehicle::firstOrCreate(['license_plate' => 'B 4567 TZX'], [
            'customer_id' => $cust2->id,
            'brand' => 'Yamaha',
            'model' => 'NMAX 155 Connected',
            'year' => 2022,
            'color' => 'Biru Doff',
            'last_mileage' => 21800,
            'is_active' => true,
        ]);

        $cust3 = Customer::firstOrCreate(['customer_code' => 'CUST-0003'], [
            'name' => 'Reza Fahlevi',
            'phone' => '087890123456',
            'address' => 'Jl. Fatmawati No. 78, Jaksel',
            'is_active' => true,
        ]);

        $veh3 = Vehicle::firstOrCreate(['license_plate' => 'B 6789 ABC'], [
            'customer_id' => $cust3->id,
            'brand' => 'Honda',
            'model' => 'Beat FI Deluxe',
            'year' => 2021,
            'color' => 'Silver',
            'last_mileage' => 28500,
            'is_active' => true,
        ]);

        // 11. Transaction Scenario 1: Work Order Selesai, Invoice Lunas (Cash)
        $rec1 = ServiceReception::firstOrCreate(['reception_number' => 'REC/'.date('Ym').'/0001'], [
            'customer_id' => $cust1->id,
            'vehicle_id' => $veh1->id,
            'receiver_id' => $users['sa']->id,
            'mileage_in' => 14250,
            'customer_complaint' => 'Tarikan gas berat, ada getaran saat awal jalan (gredeg pada CVT)',
            'initial_condition' => 'Bensin 3 bar, ada helm titip di spion kiri, body lecet pemakaian',
            'reception_time' => now()->subDays(2)->setTime(9, 30),
            'status' => 'completed',
        ]);

        $wo1 = WorkOrder::firstOrCreate(['wo_number' => 'WO/'.date('Ym').'/0001'], [
            'reception_id' => $rec1->id,
            'customer_id' => $cust1->id,
            'vehicle_id' => $veh1->id,
            'mileage' => 14250,
            'customer_complaint' => 'Tarikan gas berat, ada getaran saat awal jalan (gredeg pada CVT)',
            'inspection_result' => 'Mangkok ganda CVT kotor debu, oli mesin sudah hitam pekat, kampas rem depan tipis',
            'estimated_cost' => 216000,
            'actual_total_cost' => 216000,
            'status' => 'closed',
            'start_time' => now()->subDays(2)->setTime(10, 0),
            'completion_time' => now()->subDays(2)->setTime(11, 45),
            'created_by' => $users['sa']->id,
        ]);

        // Add WO items
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Servis CVT Matic Lengkap'], [
            'item_type' => 'service',
            'service_id' => ServiceCatalog::where('code', 'SRV-002')->first()->id,
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Jasa Ganti Oli Mesin & Gardan'], [
            'item_type' => 'service',
            'service_id' => ServiceCatalog::where('code', 'SRV-003')->first()->id,
            'quantity' => 1,
            'unit_price' => 15000,
            'subtotal' => 15000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Oli Mesin MPX2 Matic 0.8L'], [
            'item_type' => 'part',
            'spare_part_id' => $parts['PRT-OLI-001']->id,
            'quantity' => 1,
            'unit_price' => 58000,
            'subtotal' => 58000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Oli Gardan Matic 120ml'], [
            'item_type' => 'part',
            'spare_part_id' => $parts['PRT-OLI-002']->id,
            'quantity' => 1,
            'unit_price' => 18000,
            'subtotal' => 18000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Kampas Rem Depan Vario/Beat'], [
            'item_type' => 'part',
            'spare_part_id' => $parts['PRT-REM-001']->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);

        $wo1->mechanics()->syncWithoutDetaching([
            $mech1->id => ['started_at' => now()->subDays(2)->setTime(10, 0), 'finished_at' => now()->subDays(2)->setTime(11, 45), 'role_description' => 'Mekanik Utama'],
        ]);

        WorkOrderApproval::firstOrCreate(['work_order_id' => $wo1->id, 'version' => 1], [
            'estimated_amount' => 216000,
            'approved_at' => now()->subDays(2)->setTime(9, 45),
            'approval_method' => 'digital_signature',
            'recorded_by' => $users['sa']->id,
            'notes' => 'Pelanggan menandatangani persetujuan di meja Service Advisor',
        ]);

        WorkOrderInspection::firstOrCreate(['work_order_id' => $wo1->id], [
            'inspector_id' => $users['sa']->id,
            'inspected_at' => now()->subDays(2)->setTime(11, 50),
            'brake_check' => 'ok',
            'engine_check' => 'ok',
            'electric_check' => 'ok',
            'tires_check' => 'ok',
            'test_drive_notes' => 'Getaran CVT hilang total, rem depan pakem dan responsif',
            'is_passed' => true,
        ]);

        // Invoice for WO 1
        $inv1 = Invoice::firstOrCreate(['invoice_number' => 'INV/'.date('Ym').'/0001'], [
            'work_order_id' => $wo1->id,
            'customer_id' => $cust1->id,
            'invoice_date' => now()->subDays(2)->toDateString(),
            'due_date' => now()->subDays(2)->toDateString(),
            'subtotal_services' => 90000,
            'subtotal_parts' => 126000,
            'subtotal' => 216000,
            'discount' => 16000, // Diskon promo Rp16.000
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'grand_total' => 200000,
            'paid_amount' => 200000,
            'balance_due' => 0,
            'status' => 'paid',
            'created_by' => $users['cashier']->id,
        ]);

        Payment::firstOrCreate(['payment_number' => 'PAY/'.date('Ym').'/0001'], [
            'invoice_id' => $inv1->id,
            'amount' => 200000,
            'payment_method' => 'cash',
            'payment_date' => now()->subDays(2)->toDateString(),
            'received_by' => $users['cashier']->id,
            'reference_number' => 'CASH-001',
            'notes' => 'Pembayaran lunas uang pas di kasir',
            'status' => 'valid',
        ]);

        // 12. Transaction Scenario 2: Work Order Selesai, Invoice Sebagian (Piutang)
        $wo2 = WorkOrder::firstOrCreate(['wo_number' => 'WO/'.date('Ym').'/0002'], [
            'customer_id' => $cust2->id,
            'vehicle_id' => $veh2->id,
            'mileage' => 21800,
            'customer_complaint' => 'Servis berkala 20.000 km, ganti oli dan ganti V-belt transmisi',
            'inspection_result' => 'V-belt mulai retak mikro, roller CVT aus gepeng',
            'estimated_cost' => 373000,
            'actual_total_cost' => 373000,
            'status' => 'invoiced',
            'start_time' => now()->subDay()->setTime(13, 0),
            'completion_time' => now()->subDay()->setTime(15, 0),
            'created_by' => $users['sa']->id,
        ]);

        WorkOrderItem::firstOrCreate(['work_order_id' => $wo2->id, 'item_name' => 'Servis Ringan / Rutin'], [
            'item_type' => 'service',
            'service_id' => ServiceCatalog::where('code', 'SRV-001')->first()->id,
            'quantity' => 1,
            'unit_price' => 60000,
            'subtotal' => 60000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo2->id, 'item_name' => 'V-Belt Kit Honda Vario 150/160'], [
            'item_type' => 'part',
            'spare_part_id' => $parts['PRT-CVT-001']->id,
            'quantity' => 1,
            'unit_price' => 185000,
            'subtotal' => 185000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo2->id, 'item_name' => 'Filter Udara Vario 160 / PCX 160'], [
            'item_type' => 'part',
            'spare_part_id' => $parts['PRT-FLT-001']->id,
            'quantity' => 1,
            'unit_price' => 62000,
            'subtotal' => 62000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);

        $wo2->mechanics()->syncWithoutDetaching([
            $mech2->id => ['started_at' => now()->subDay()->setTime(13, 0), 'finished_at' => now()->subDay()->setTime(15, 0), 'role_description' => 'Mekanik'],
        ]);

        WorkOrderApproval::firstOrCreate(['work_order_id' => $wo2->id, 'version' => 1], [
            'estimated_amount' => 373000,
            'approved_at' => now()->subDay()->setTime(12, 45),
            'approval_method' => 'phone_wa',
            'recorded_by' => $users['sa']->id,
            'notes' => 'Persetujuan via WhatsApp pesan suara oleh Ibu Dewi',
        ]);

        WorkOrderInspection::firstOrCreate(['work_order_id' => $wo2->id], [
            'inspector_id' => $users['sa']->id,
            'inspected_at' => now()->subDay()->setTime(15, 10),
            'brake_check' => 'ok',
            'engine_check' => 'ok',
            'electric_check' => 'ok',
            'tires_check' => 'ok',
            'is_passed' => true,
        ]);

        $inv2 = Invoice::firstOrCreate(['invoice_number' => 'INV/'.date('Ym').'/0002'], [
            'work_order_id' => $wo2->id,
            'customer_id' => $cust2->id,
            'invoice_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'subtotal_services' => 60000,
            'subtotal_parts' => 247000,
            'subtotal' => 307000,
            'discount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'grand_total' => 307000,
            'paid_amount' => 150000,
            'balance_due' => 157000,
            'status' => 'partially_paid',
            'created_by' => $users['cashier']->id,
        ]);

        Payment::firstOrCreate(['payment_number' => 'PAY/'.date('Ym').'/0002'], [
            'invoice_id' => $inv2->id,
            'amount' => 150000,
            'payment_method' => 'qris',
            'payment_date' => now()->subDay()->toDateString(),
            'received_by' => $users['cashier']->id,
            'reference_number' => 'QRIS-NMAX-DP',
            'notes' => 'Uang muka / DP servis via QRIS, sisa dilunasi saat ambil sore hari',
            'status' => 'valid',
        ]);

        // 13. Transaction Scenario 3: Sedang Dikerjakan (In Progress)
        $wo3 = WorkOrder::firstOrCreate(['wo_number' => 'WO/'.date('Ym').'/0003'], [
            'customer_id' => $cust3->id,
            'vehicle_id' => $veh3->id,
            'mileage' => 28500,
            'customer_complaint' => 'Rem belakang kurang pakem dan lampu rem mati',
            'inspection_result' => 'Kampas rem tromol habis, bohlam lampu rem putus filamen',
            'estimated_cost' => 80000,
            'actual_total_cost' => 80000,
            'status' => 'in_progress',
            'start_time' => now()->subHours(2),
            'created_by' => $users['sa']->id,
        ]);

        WorkOrderItem::firstOrCreate(['work_order_id' => $wo3->id, 'item_name' => 'Jasa Ganti Kampas Rem Depan / Belakang'], [
            'item_type' => 'service',
            'service_id' => ServiceCatalog::where('code', 'SRV-005')->first()->id,
            'quantity' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);
        WorkOrderItem::firstOrCreate(['work_order_id' => $wo3->id, 'item_name' => 'Kampas Rem Belakang Tromol Matic'], [
            'item_type' => 'part',
            'spare_part_id' => $parts['PRT-REM-002']->id,
            'quantity' => 1,
            'unit_price' => 55000,
            'subtotal' => 55000,
            'is_approved' => true,
            'fulfillment_status' => 'issued',
        ]);

        $wo3->mechanics()->syncWithoutDetaching([
            $mech3->id => ['started_at' => now()->subHours(2), 'role_description' => 'Mekanik Pengereman'],
        ]);

        WorkOrderApproval::firstOrCreate(['work_order_id' => $wo3->id, 'version' => 1], [
            'estimated_amount' => 80000,
            'approved_at' => now()->subHours(2)->subMinutes(15),
            'approval_method' => 'in_person',
            'recorded_by' => $users['sa']->id,
            'notes' => 'Persetujuan langsung pelanggan saat estimasi di bengkel',
        ]);

        // Audit Logs sample
        AuditLog::create([
            'user_id' => $users['owner']->id,
            'action' => 'setup',
            'module' => 'System',
            'summary' => 'Inisialisasi sistem MotoCare Workshop Management System selesai.',
            'created_at' => now()->subDays(5),
        ]);
    }
}
