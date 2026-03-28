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
    Schema::create('promocions', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('descripcion');
        $table->decimal('descuento', 8, 2);
        $table->string('tipo')->default('activa');
        $table->boolean('activa')->default(true);
        $table->unsignedBigInteger('producto1_id')->nullable();
        $table->unsignedBigInteger('producto2_id')->nullable();
        $table->timestamps();

        $table->foreign('producto1_id')->references('id')->on('productos')->onDelete('set null');
        $table->foreign('producto2_id')->references('id')->on('productos')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocions');
    }
};
