<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $fillable = [
        'title',
        'description',
    ];

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($news) {
            $news->photos->each->delete();
        });
    }
}
