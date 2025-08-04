<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;

class PetitionPrice extends Model
{
    protected $fillable = ['type', 'amount', 'description'];


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

}
