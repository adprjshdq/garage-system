<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('license_plate'); // nomor polisi
            $table->string('brand'); // Honda, Yamaha, Suzuki, Kawasaki, Vespa, dll
            $table->string('model'); // Vario 160, NMAX 155, Beat, dll
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->string('vin_number')->nullable(); // nomor rangka
            $table->string('engine_number')->nullable(); // nomor mesin
            $table->unsignedInteger('last_mileage')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('license_plate');
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('customers');
    }
};
