<?php

namespace App\Console\Commands;

use App\Models\IndexState;
use App\Processor\IndexProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IndexingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:execute {--full} {--increment} {--start-queue-worker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes files';

    protected IndexProcessor $indexProcessor;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IndexProcessor $indexProcessor)
    {
        parent::__construct();
        $this->indexProcessor = $indexProcessor;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('start-queue-worker')) {
            $this->executeQueueHandler();
        } elseif ($this->option('full')) {
            $this->indexProcessor->completeIndexUpdate();
        } elseif ($this->option('increment')) {
            $this->indexProcessor->incrementIndexUpdate();
        } else {
            throw new \InvalidArgumentException('no option provided');
        }
        return 0;
    }

    private function executeQueueHandler() {
        print_r('queue worker started'. PHP_EOL);
        do {
            try {
                $indexState = IndexState::query()->findOrFail(1)->first();
                if ($indexState['state'] === IndexState::STATE_TRIGGERED) {
                    $this->indexProcessor->completeIndexUpdate();
                }
                sleep(5);
            } catch (\Exception $exception) {
                //Keep the worker alive but log the error message and wait 30 secons before trying to execute again
                Log::error($exception->getMessage());
                sleep(30);
            }
        } while (true);
    }
}
