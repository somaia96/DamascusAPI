<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'number',
        'description',
        'status',
    ];

    protected $nullable = ['name', 'number'];

    protected $attributes = [
        'status' => 'unresolved',
    ];

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
