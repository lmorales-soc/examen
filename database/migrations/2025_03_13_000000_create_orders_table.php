<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('cliente');
            $table->string('producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->date('fecha');
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['Pendiente', 'Procesada', 'Cancelada'])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
