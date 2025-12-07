<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouncilMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_id',
        'job_title', // Add job_title to fillable
        'description', // Add description to fillable
    ];

    public function photo()
    {
        return $this->morphOne(Photo::class, 'photoable');
    }
}
