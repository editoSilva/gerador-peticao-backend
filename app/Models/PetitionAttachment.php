<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PetitionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'petition_request_id',
        'file_path',
        'file_type',
    ];

    public function petitionRequest()
    {
        return $this->belongsTo(PetitionRequest::class);
    }
}
