<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->change();
            $table->boolean('serial_pending')->default(false)->after('serial_number');
            $table->string('ownership_type')->default('mua_sam')->after('condition');
            $table->text('origin_model_text')->nullable()->after('ownership_type');
            $table->string('source_ref')->nullable()->after('origin_model_text');
        });

        Schema::create('equipment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('status')->default('chua_ra_soat');
            $table->string('file_path')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['equipment_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_documents');

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['serial_pending', 'ownership_type', 'origin_model_text', 'source_ref']);
            $table->string('serial_number')->nullable(false)->change();
        });
    }
};
