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
        Schema::create('petitions', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id')->nullable()->index();        
            $table->string('type')->nullable();
            $table->longText('content');
            $table->json('input_data');
            $table->string('pdf_url')->nullable();
            $table->longText('local_delivery')->nullable();
            $table->enum('status', ['paid', 'pending', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petitions');
    }
};
