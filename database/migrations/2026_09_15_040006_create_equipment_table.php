<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('equipment_model_id')->constrained('equipment_models');
            $table->foreignId('category_id')->constrained('equipment_categories');
            $table->foreignId('manufacturer_id')->constrained('manufacturers');
            $table->string('serial_number')->unique();
            $table->integer('manufacture_year')->nullable();
            $table->string('origin_country')->nullable();
            $table->date('import_date')->nullable();
            $table->date('usage_start_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('warranty_expiration_date')->nullable();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('manager_name')->nullable();
            $table->string('status')->default('dang_su_dung');
            $table->string('condition')->default('dang_hoat_dong');
            $table->timestamp('delete_requested_at')->nullable();
            $table->foreignId('delete_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('import_batch_id')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
