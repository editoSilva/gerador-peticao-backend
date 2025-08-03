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
        Schema::create('petition_requests', function (Blueprint $table) {
            $table->id();
            $table->text('prompt')->nullable();
            $table->string('nome_completo');
            $table->string('cpf', 20);
            $table->string('rg', 20)->nullable();
            $table->string('orgao_expedidor', 50)->nullable();
            $table->string('estado_civil', 50)->nullable();
            $table->string('profissao', 100)->nullable();
            $table->string('endereco');
            $table->string('cidade', 100);
            $table->string('estado', 2);
            $table->string('cep', 20);
            $table->string('requerido')->nullable();
            $table->string('email')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('cnpj', 20)->nullable();
            $table->enum('status', ['pending', 'paid', 'refused', 'refunded', 'chargeback'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petition_requests');
    }
};
