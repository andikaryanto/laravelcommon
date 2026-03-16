<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('selected_pricing_id')->nullable()->after('pricing_id');

            $table->foreign('selected_pricing_id')
                ->references('id')->on('pricings')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign('branches_selected_pricing_id_foreign');
            $table->dropColumn('selected_pricing_id');
        });
    }
};