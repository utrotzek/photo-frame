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

    /**
     * Returns the current index state
     */
    public static function getCurrent(): IndexState
    {
        return self::query()->findOrFail(1)->first();
    }

    public function setTriggered()
    {
        $this->state = IndexState::STATE_TRIGGERED;
        $this->total = 0;
        $this->current = 0;
        $this->retries = 0;
        $this->message = '';
        $this->save();
    }

    public function setAbort()
    {
        $this->state = IndexState::STATE_ABORT;
        $this->message = 'Indexierung abgebrochen';
        $this->save();
    }

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
            $rounded = round($this->current / $this->total * 100, 2);
            return number_format($rounded, 2);
        } else {
          return 0.00;
        }
    }

    public static function isAborted(): bool
    {
        $indexState = IndexState::query()->findOrFail(1)->first();
        return ($indexState['state'] === self::STATE_ABORT);
    }
}
