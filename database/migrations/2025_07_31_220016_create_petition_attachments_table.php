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
        Schema::create('petition_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petition_request_id')->constrained('petition_requests')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petition_attachments');
    }
};
