<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_pengantaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supir_id')->nullable()->constrained('tbl_supir');
            $table->date('tanggal_pengantaran');
            $table->enum('metode_pengiriman', ['Pickup', 'Delivery']);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.fe
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pengantaran');
    }
};
