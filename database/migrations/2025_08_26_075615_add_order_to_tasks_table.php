<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderToTasksTable extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('level');
        });

        // Set urutan default berdasarkan id
        $tasks = \App\Models\Task::orderBy('id')->get();
        foreach ($tasks as $index => $task) {
            $task->update(['order' => $index + 1]);
        }
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}