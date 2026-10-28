<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_part_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('spare_part_categories')->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default('Pcs'); // Pcs, Botol, Liter, Set, dll
            $table->decimal('reference_cost_price', 15, 2)->default(0); // Harga beli referensi / HPP
            $table->decimal('selling_price', 15, 2)->default(0); // Harga jual
            $table->decimal('stock_quantity', 10, 2)->default(0); // Fisik di bengkel
            $table->decimal('reserved_quantity', 10, 2)->default(0); // Di-booking oleh WO approved
            $table->decimal('min_stock', 10, 2)->default(5); // Alert stok menipis
            $table->string('rack_location')->nullable(); // Rak A-1, B-2, dll
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type'); // in, out, adjustment, return, initial
            $table->decimal('quantity', 10, 2); // Nilai mutasi (+ untuk in/retur, - untuk out)
            $table->decimal('stock_before', 10, 2)->default(0);
            $table->decimal('stock_after', 10, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->string('reference_type')->nullable(); // purchase_order, work_order, adjustment, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['spare_part_id', 'created_at']);
            $table->index('reference_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('spare_parts');
        Schema::dropIfExists('spare_part_categories');
    }
};
