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
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('products');
        Schema::dropIfExists('users');
        Schema::dropIfExists('product');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('apellido');
        $table->string("username")->unique();    
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('url_image');
        $table->timestamps();
    });

    Schema::create('favorites', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        $table->timestamps();
    });
    }
};
