<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurisprudence extends Model
{

    protected $fillable = [
        'title',             // Título da jurisprudência
        'summary',           // Ementa ou resumo
        'full_text',         // Texto completo
        'court',             // Órgão julgador
        'case_number',       // Número do processo
        'judgment_date',     // Data do julgamento
        'reporting_judge',   // Nome do relator
        'keywords',          // Palavras-chave
        'source',            // Fonte (ex: URL)
    ];


    protected $casts = [
        'judgment_date' => 'date',
    ];

    public function petitions()
    {
        return $this->belongsToMany(Petition::class);
    }
}
