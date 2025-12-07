<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'activity_date',
        'description',
        'activity_type_id',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function activityType()
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }
}
