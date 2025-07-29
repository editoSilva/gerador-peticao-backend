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
        Schema::create('jurisprudences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->text('summary')->nullable();
            $table->longText('full_text');
            $table->string('court')->nullable();
            $table->string('case_number')->nullable();
            $table->date('judgment_date')->nullable();
            $table->string('reporting_judge')->nullable();
            $table->string('keywords')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurisprudences');
    }
};
