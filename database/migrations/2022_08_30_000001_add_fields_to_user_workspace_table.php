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
        Schema::table('user_workspace', function (Blueprint $table) {

            $table->string('work_role')->default('member')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['work_role']);
        });
    }
};
