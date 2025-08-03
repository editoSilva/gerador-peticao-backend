<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PetitionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt',
        'phone',
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
        'status',
        'jurisprudences',
        'ref_id',
        'price',
        'qr_code',
        'type',
        'placa',
        'data',
        'local',
        'infracao',
        'orgao_atuador',
        'numero_auto_infra'
    ];

   
    public function attachments()
    {
        return $this->hasMany(PetitionAttachment::class);
    }
}
