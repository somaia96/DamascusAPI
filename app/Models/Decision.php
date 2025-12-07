<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    use HasFactory;

    protected $fillable = [
        'decision_id',
        'decision_date',
        'title',
        'description',
    ];

    protected $casts = [
        'decision_id' => 'integer',
        'decision_date' => 'date',
    ];

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}