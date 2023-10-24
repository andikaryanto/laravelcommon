<?php

use Illuminate\Database\Migrations\Migration;
use LaravelCommon\System\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupuserScopeMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groupuser_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('groupuser_id');
            $table->auditable();
            $table->timestamps();


            $table->foreign('groupuser_id')
                ->references('id')->on('groupusers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groupuser_scopes');
    }
}
