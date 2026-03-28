<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('insumos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('categoria');
        $table->integer('cantidad')->default(0); // <--- ESTA ES LA QUE FALTA
        $table->string('unidad');
        $table->integer('nivel_minimo')->default(10);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};