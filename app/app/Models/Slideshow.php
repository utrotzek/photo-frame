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
    const ACTION_FORWARD = 'forward';
    const ACTION_BACKWARD = 'backward';
    const ACTION_START_QUEUE = 'start_queue';
    const ACTION_restart = 'restart';

    public static function findByDevice($device): self
    {
        return self::query()
            ->where('device', '=', $device)
            ->get()
            ->first();
    }
}
