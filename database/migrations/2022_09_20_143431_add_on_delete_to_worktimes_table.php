<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('worktimes', function (Blueprint $table) {
            $table->dropIndex('worktimes_task_id_index');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('worktimes', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->index('task_id');
        });
    }
};
