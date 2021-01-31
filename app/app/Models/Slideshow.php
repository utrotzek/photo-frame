<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slideshow extends Model
{
    use HasFactory;

    protected $table = 'slideshow';

    protected $fillable = [
        'action',
        'device',
        'next_action',
        'queue_title',
        'next_queue_title'
    ];

    const ACTION_PLAY = 'play';
    const ACTION_PAUSE = 'pause';
    const ACTION_STOP = 'stop';
    const ACTION_PREV = 'prev';
    const ACTION_NEXT = 'next';
    const ACTION_START_QUEUE = 'start_queue';
    const ACTION_RESTART = 'restart';
    const ACTION_UPDATE_SETTINGS_DURATION = 'settings_duration';
    const SLIDESHOW_ACTION_RELOAD_CURRENT = 'reload_current';
    const SLIDESHOW_ACTION_ADD_FAVORITE = 'add_favorite';
    const SLIDESHOW_ACTION_REMOVE_FAVORITE = 'remove_favorite';

    public static function findByDevice($device): self
    {
        return self::query()
            ->where('device', '=', $device)
            ->get()
            ->first();
    }
}
