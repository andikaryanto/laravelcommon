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
        Schema::create('pricing_application_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pricing_id');
            $table->unsignedBigInteger('application_feature_id');
            $table->auditable();
            $table->timestamps();

            $table->foreign('pricing_id')
                ->references('id')->on('pricings')->onDelete('cascade');

            $table->foreign('application_feature_id')
                ->references('id')->on('application_features')->onDelete('restrict');

            $table->unique(['pricing_id', 'application_feature_id'], 'pricing_application_features_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_application_features', function (Blueprint $table) {
            $table->dropUnique('pricing_application_features_unique');
            $table->dropForeign('pricing_application_features_pricing_id_foreign');
            $table->dropForeign('pricing_application_features_application_feature_id_foreign');
        });

        Schema::dropIfExists('pricing_application_features');
    }
};
