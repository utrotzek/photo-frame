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
        $this->state = IndexState::STATE_WORKING;
        $this->total = $count;
        $this->current = 0;
        $this->retries = 0;
        $this->last_run = new \DateTime();
        $this->save();
    }

    public function setFinished()
    {
        $this->state = IndexState::STATE_WAITING;
        $this->current = 0;
        $this->total = 0;
        $this->save();
    }

    public function setFailed($message)
    {
        $this->state = IndexState::STATE_FAILED;
        $this->message = $message;
        $this->retries++;
        $this->save();
    }
}
