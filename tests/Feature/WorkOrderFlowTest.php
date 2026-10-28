<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Mechanic;
use App\Models\Role;
use App\Models\ServiceCatalog;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorkshopSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Mechanic $mechanic;

    protected Customer $customer;

    protected Vehicle $vehicle;

    protected ServiceCatalog $service;

    protected SparePart $part;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(WorkshopSettingSeeder::class);

        $ownerRole = Role::where('name', 'owner')->first();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->user->roles()->attach($ownerRole->id);

        $this->mechanic = Mechanic::create([
            'code' => 'MECH-001',
            'name' => 'Agus Gunawan',
            'phone' => '081277778888',
            'specialization' => 'CVT & Mesin',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-0001',
            'name' => 'Budi Gunawan',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'address' => 'Jl. Merdeka No. 10',
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'customer_id' => $this->customer->id,
            'license_plate' => 'B 1234 ABC',
            'brand' => 'Honda',
            'model' => 'Vario 150',
            'year' => 2021,
            'color' => 'Hitam',
            'engine_number' => 'ENG123456',
            'chassis_number' => 'CHS123456',
            'last_mileage' => 15000,
        ]);

        $this->service = ServiceCatalog::create([
            'code' => 'SRV-001',
            'name' => 'Tune Up Matic',
            'category' => 'tune_up',
            'price' => 75000,
            'estimated_duration_minutes' => 45,
            'is_active' => true,
        ]);

        $category = SparePartCategory::create([
            'code' => 'CAT-01',
            'name' => 'Pelumas',
        ]);

        $this->part = SparePart::create([
            'category_id' => $category->id,
            'sku' => 'PART-001',
            'name' => 'Oli Mesin MPX2',
            'brand' => 'AHM',
            'unit' => 'botol',
            'purchase_price' => 45000,
            'selling_price' => 55000,
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_work_orders(): void
    {
        $response = $this->get(route('work-orders.index'));
        $response->assertRedirect(route('login'));

        $createResponse = $this->get(route('work-orders.create'));
        $createResponse->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_work_orders_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('work-orders.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Work Order');
        $response->assertSee($this->customer->name);
        $response->assertSee($this->service->name);
        $response->assertSee($this->part->name);
    }

    public function test_user_can_create_work_order_with_service_and_parts(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'mileage' => 15200,
            'customer_complaint' => 'Tarikan gas berat dan getar di putaran bawah',
            'inspection_result' => 'Roller aus dan oli kotor',
            'notes' => 'Harap dicek juga kampas rem',
            'items' => [
                [
                    'item_type' => 'service',
                    'service_id' => $this->service->id,
                    'item_name' => $this->service->name,
                    'quantity' => 1,
                    'unit_price' => 75000,
                    'discount' => 0,
                ],
                [
                    'item_type' => 'part',
                    'spare_part_id' => $this->part->id,
                    'item_name' => $this->part->name,
                    'quantity' => 1,
                    'unit_price' => 55000,
                    'discount' => 5000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('work-orders.store'), $payload);

        $this->assertDatabaseHas('work_orders', [
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'mileage' => 15200,
            'status' => 'draft',
            'estimated_cost' => 125000, // 75000 + (55000 - 5000)
        ]);

        $wo = WorkOrder::latest('id')->first();
        $this->assertNotNull($wo);
        $response->assertRedirect(route('work-orders.show', $wo->id));
        $this->assertCount(2, $wo->items);
    }

    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $response = $this->actingAs($this->user)->post(route('work-orders.store'), [
            'customer_id' => '',
            'vehicle_id' => '',
            'mileage' => '',
            'customer_complaint' => '',
            'items' => [],
        ]);

        $response->assertSessionHasErrors(['customer_id', 'vehicle_id', 'mileage', 'customer_complaint', 'items']);
    }

    public function test_full_work_order_lifecycle(): void
    {
        // 1. Create Work Order in Draft
        $payload = [
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'mileage' => 15500,
            'customer_complaint' => 'Servis berkala dan ganti oli',
            'items' => [
                [
                    'item_type' => 'service',
                    'service_id' => $this->service->id,
                    'item_name' => $this->service->name,
                    'quantity' => 1,
                    'unit_price' => 75000,
                    'discount' => 0,
                ],
                [
                    'item_type' => 'part',
                    'spare_part_id' => $this->part->id,
                    'item_name' => $this->part->name,
                    'quantity' => 1,
                    'unit_price' => 55000,
                    'discount' => 0,
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('work-orders.store'), $payload);
        $wo = WorkOrder::latest('id')->first();
        $this->assertEquals('draft', $wo->status);

        // 2. Approve Work Order
        $approveResponse = $this->actingAs($this->user)->post(route('work-orders.approve', $wo->id), [
            'approval_method' => 'in_person',
            'notes' => 'Disetujui langsung oleh pelanggan',
        ]);
        $approveResponse->assertSessionHas('success');
        $wo->refresh();
        $this->assertEquals('approved', $wo->status);

        // 3. Start Work Order
        $startResponse = $this->actingAs($this->user)->post(route('work-orders.start', $wo->id), [
            'mechanic_ids' => [$this->mechanic->id],
        ]);
        $startResponse->assertSessionHas('success');
        $wo->refresh();
        $this->assertEquals('in_progress', $wo->status);
        $this->assertTrue($wo->mechanics->contains($this->mechanic->id));

        // 4. Issue Spare Part
        $partItem = $wo->partItems()->first();
        $this->assertNotNull($partItem);
        $initialStock = $this->part->fresh()->stock_quantity;

        $issueResponse = $this->actingAs($this->user)->post(
            route('work-orders.issue-part', ['work_order' => $wo->id, 'item' => $partItem->id])
        );
        $issueResponse->assertSessionHas('success');
        $partItem->refresh();
        $this->assertEquals('issued', $partItem->fulfillment_status);
        $this->assertEquals($initialStock - 1, $this->part->fresh()->stock_quantity);

        // 5. Final Inspection & Completion
        $inspectResponse = $this->actingAs($this->user)->post(route('work-orders.inspect', $wo->id), [
            'brake_check' => 'ok',
            'engine_check' => 'ok',
            'electric_check' => 'ok',
            'tires_check' => 'ok',
            'test_drive_notes' => 'Mesin halus, rem pakem',
            'is_passed' => 1,
            'notes' => 'Pengerjaan selesai dengan baik',
        ]);
        $inspectResponse->assertSessionHas('success');
        $wo->refresh();
        $this->assertEquals('completed', $wo->status);
        $this->assertNotNull($wo->completion_time);
        $this->assertEquals(15500, $this->vehicle->fresh()->last_mileage);
    }
}
