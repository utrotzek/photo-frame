<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndexState extends Model
{
    use HasFactory;

    const STATE_WAITING = 'waiting';
    const STATE_STARTING = 'starting';
    const STATE_WORKING = 'working';
    const STATE_FAILED = 'failed';
    const STATE_TRIGGERED = 'triggered';
    const STATE_ABORT = 'abort';

    protected $fillable = [
        'state',
        'current',
        'total',
        'message',
        'last_run',
    ];

    protected $casts = [
        'last_run' => 'datetime'
    ];

    public function setStarting()
    {
        $this->state = IndexState::STATE_STARTING;
        $this->total = 0;
        $this->current = 0;
        $this->retries = 0;
        $this->message = '';
        $this->last_run = new Carbon();
        $this->save();
    }


    public function setWorking($count)
    {
        $this->state = IndexState::STATE_WORKING;
        $this->total = $count;
        $this->current = 0;
        $this->retries = 0;
        $this->save();
    }

    public function setFinished(DateTime $indexTime)
    {
        $this->state = IndexState::STATE_WAITING;
        $this->current = 0;
        $this->total = 0;
        $this->last_run = $indexTime;
        $this->save();
    }

    public function setFailed($message)
    {
        $this->state = IndexState::STATE_FAILED;
        $this->message = $message;
        $this->retries++;
        $this->save();
    }

    public function getPercentage(): float
    {
        if ($this->total > 0) {
            return round($this->current / $this->total * 100, 2);
        } else {
          return 0.00;
        }
    }
}
