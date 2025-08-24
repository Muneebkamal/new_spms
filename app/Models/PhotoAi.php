<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoAi extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'photo_id',
        'img_name',
        'preset',
        'style',
        'prompt',
        'code',
    ];

    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
}
