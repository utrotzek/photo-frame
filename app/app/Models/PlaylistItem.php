<?php

namespace App\Models;

use App\Utility\PathUtility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistItem extends Model
{
    use HasFactory;
    protected $table = 'playlist_item';
    protected $fillable = ['path'];

    public function setPathAttribute(string $value)
    {
        $this->attributes['path'] = PathUtility::getInternalPath($value);
    }
}
