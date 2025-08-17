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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('document', 20)->unique()->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('document');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0);
        });
    }
};
