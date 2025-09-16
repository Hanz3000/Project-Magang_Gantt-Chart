<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('nip', 8)->unique()->nullable(); 
        // string dengan panjang maksimal 8
        // unique biar tidak ada duplikat
        // nullable kalau mau opsional
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('nip');
    });
}

};
