<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndexState extends Model
{
    use HasFactory;

    const STATE_WAITING = 'waiting';
    const STATE_WORKING = 'working';
    const STATE_FAILED = 'failed';

    protected $fillable = [
        'state',
        'current',
        'total',
        'message',
        'last_run',
    ];

    public function setWorking($count)
    {
        $this->attributes['state'] = IndexState::STATE_WORKING;
        $this->attributes['total'] = $count;
        $this->attributes['current'] = 0;
        $this->attributes['last_run'] = new \DateTime();
        $this->save();
    }

    public function setFinished()
    {
        $this->attributes['state'] = IndexState::STATE_WAITING;
        $this->attributes['current'] = 0;
        $this->attributes['total'] = 0;
        $this->save();
    }
}
