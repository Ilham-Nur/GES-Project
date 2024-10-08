<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tbl_jurnal', function (Blueprint $table) {
            $table->id();
            $table->string('no_journal')->unique();
            $table->string('tipe_kode');
            $table->date('tanggal');
            $table->string('no_ref');
            $table->string('status');
            $table->text('description');
            $table->decimal('totalcredit', 15, 2)->default(0);
            $table->decimal('totaldebit', 15, 2)->default(0);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_jurnal');
    }
};
