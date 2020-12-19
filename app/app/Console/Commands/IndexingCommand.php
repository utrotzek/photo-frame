<?php

namespace App\Console\Commands;

use App\Tasks\FileIndexTask;
use Illuminate\Console\Command;

class IndexingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:execute {--full}';

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
        if ($this->option('full')){
            $this->fileIndexTask->completeIndexUpdate();
        }else{
            //increment
            throw new \Exception("Not implemented");
        }
        return 0;
    }
}
