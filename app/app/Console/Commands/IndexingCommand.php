<?php

namespace App\Console\Commands;

use App\Models\IndexState;
use App\Tasks\FileIndexTask;
use Illuminate\Console\Command;

class IndexingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:execute {--full} {--increment} {--check-queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes files';

    protected FileIndexTask $fileIndexTask;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FileIndexTask $fileIndexTask)
    {
        parent::__construct();
        $this->fileIndexTask = $fileIndexTask;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('check-queue')) {
            $indexState = IndexState::query()->findOrFail(1)->first();
            if ($indexState['state'] === IndexState::STATE_TRIGGERED) {
                $this->fileIndexTask->completeIndexUpdate();
            }
        } elseif ($this->option('full')) {
            $this->fileIndexTask->completeIndexUpdate();
        } elseif ($this->option('incremenct')) {
            $this->fileIndexTask->incrementIndexUpdate();
        } else {
            throw new \InvalidArgumentException('no option provided');
        }
        return 0;
    }
}
