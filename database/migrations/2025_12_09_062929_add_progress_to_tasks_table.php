<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // Cek dulu, jika kolom 'progress' BELUM ada, baru buat
    if (!Schema::hasColumn('tasks', 'progress')) {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('progress')->default(0)->after('finish');
        });
    }
}

public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropColumn('progress');
    });
}
};
