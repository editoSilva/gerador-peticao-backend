<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Petition extends Model
{
    protected $fillable = [
        'type',
        'content',
        'input_data',
        'ref_id',
        'local_delivery',
        'pdf_url',
        'status',
        'origin'
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $searchableFields = $this->getFillable();

        foreach ($filters as $field => $value) {
            if (in_array($field, $searchableFields) && $value !== null) {
                $query->where($field, 'like', '%' . $value . '%');
            }
        }

        return $query;
    }

    protected $casts = 
    [
        'input_data' => 'array',
    ];

    public function jurisprudences()
    {
        return $this->belongsToMany(Jurisprudence::class);
    }
}
