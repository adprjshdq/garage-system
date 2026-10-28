<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_receptions', function (Blueprint $table) {
            $table->id();
            $table->string('reception_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('mileage_in');
            $table->text('customer_complaint');
            $table->text('initial_condition')->nullable(); // Bensin, baret body, helm titip, dll
            $table->text('accessories_note')->nullable();
            $table->string('photo_path')->nullable();
            $table->dateTime('reception_time');
            $table->dateTime('estimated_completion_time')->nullable();
            $table->string('status')->default('waiting_inspection'); // waiting_inspection, in_inspection, processed, completed, cancelled
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('reception_number');
            $table->index('status');
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('reception_id')->nullable()->constrained('service_receptions')->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('mileage');
            $table->text('customer_complaint');
            $table->text('inspection_result')->nullable();
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_total_cost', 15, 2)->default(0);
            $table->string('status')->default('draft');
            // draft, waiting_approval, approved, in_progress, waiting_parts, final_inspection, completed, invoiced, closed, cancelled
            $table->dateTime('start_time')->nullable();
            $table->dateTime('completion_time')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('wo_number');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // 'service' atau 'part'
            $table->foreignId('service_id')->nullable()->constrained('service_catalog')->nullOnDelete();
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('is_approved')->default(true);
            $table->string('fulfillment_status')->default('pending'); // pending, reserved, issued, returned, cancelled
            $table->timestamps();
        });

        Schema::create('work_order_mechanics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mechanic_id')->constrained()->cascadeOnDelete();
            $table->string('role_description')->nullable(); // Mekanik Utama, Pembantu, dll
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['work_order_id', 'mechanic_id']);
        });

        Schema::create('work_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->decimal('estimated_amount', 15, 2);
            $table->dateTime('approved_at');
            $table->string('approval_method'); // in_person, phone_wa, digital_signature
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->longText('signature_data')->nullable(); // Data URL PNG base64 jika tanda tangan digital
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('inspected_at');
            $table->string('brake_check')->default('ok'); // ok, need_attention, bad
            $table->string('engine_check')->default('ok');
            $table->string('electric_check')->default('ok');
            $table->string('tires_check')->default('ok');
            $table->text('test_drive_notes')->nullable();
            $table->boolean('is_passed')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_inspections');
        Schema::dropIfExists('work_order_approvals');
        Schema::dropIfExists('work_order_mechanics');
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('service_receptions');
    }
};
