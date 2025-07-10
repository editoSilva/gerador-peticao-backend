<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{
    protected $fillable = ['type', 'content', 'input_data'];

    protected $casts = [
        'input_data' => 'array',
    ];

    public function jurisprudences()
    {
        return $this->belongsToMany(Jurisprudence::class);
    }
}
