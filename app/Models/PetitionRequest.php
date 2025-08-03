<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PetitionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt',
        'nome_completo',
        'cpf',
        'rg',
        'orgao_expedidor',
        'estado_civil',
        'profissao',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'requerido',
        'email',
        'razao_social',
        'cnpj',
        'status'
    ];

    public function attachments()
    {
        return $this->hasMany(PetitionAttachment::class);
    }
}
