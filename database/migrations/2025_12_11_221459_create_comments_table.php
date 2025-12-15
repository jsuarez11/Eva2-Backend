<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // user_id: Relación con usuarios. Si borran usuario, adiós comentarios.
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();

            // product_id: Relación con productos. Si borran producto, adiós comentarios.
            $table->foreignId("product_id")->constrained()->cascadeOnDelete();

            $table->text("content"); // Contenido del comentario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
