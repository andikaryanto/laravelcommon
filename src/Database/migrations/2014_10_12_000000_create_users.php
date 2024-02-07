<?php

use Illuminate\Database\Migrations\Migration;
use LaravelCommon\System\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('groupuser_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->foreign('groupuser_id')
                ->references('id')->on('groupusers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
