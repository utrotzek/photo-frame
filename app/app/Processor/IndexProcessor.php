<?php
namespace App\Processor;

use App\Exceptions\AbortIndexingException;
use App\Models\IndexState;
use App\Models\Index;
use DateTime;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class IndexProcessor
{
    protected $indexFileExtensions = ['*.JPG', '*.jpg', '*.JPEG', '*.png', '*.PNG'];
    protected $excludedDirectories = ['Handy upload', 'Unsortiert'];

    protected $maxRetries = 7;

    protected IndexState $indexState;
    protected DateTime $indexTime;

    public function __construct()
    {
        $this->indexTime = new DateTime();
    }

    /**
     * Does a full index update by not respecting any modification dates
     * @throws \Exception
     */
    public function completeIndexUpdate()
    {
        $this->processIndexUpdate(true);
    }

    /**
     * Performs an increment index updating files which have been modified since the last run
     *
     * @throws \Exception
     */
    public function incrementIndexUpdate()
    {
       $this->processIndexUpdate();
    }

    /**
     * Processes the index update
     *
     * @param bool $fullIndex Whether to fully update the index or just incremental
     * @throws \Exception
     */
    protected function processIndexUpdate(bool $fullIndex = false)
    {
        try {
            $this->initializeIndexState();
            if ($this->indexCanRun()) {
                $finder = $this->configureFinder();

                if (!$fullIndex) {
                    //find files which have been manipulated since the last index run
                    $finder->filter(function (SplFileInfo $file){
                        return fileatime($file->getRealPath()) > strtotime($this->indexState['last_run']->format('d.m.Y H:i:s'));
                    });
                }

                $this->indexState->setStarting();
                $count = $finder->count();
                $this->checkForAbort();
                if ($count > 0){
                    $this->indexState->setWorking($count);
                    $this->processFiles($finder);
                }

                if ($fullIndex) {
                    $this->cleanUpIndex();
                }
                $this->indexState->setFinished($this->indexTime);
            }
        } catch (AbortIndexingException $exception) {
            //do nothing
        } catch (\Exception $exception) {
            $this->indexState->setFailed($exception->getMessage());
            throw new \Exception($exception);
        }
    }

    protected function indexCanRun(): bool
    {
        return
            $this->indexState['state'] !==  IndexState::STATE_WORKING ||
            (
                $this->indexState['state'] ===  IndexState::STATE_FAILED &&
                $this->indexState['retries'] < $this->maxRetries
            );
    }

    /**
     * Indexes the given files returned by the given finder instance. Throws an exception in case the indexing is
     * aborted.
     *
     * @throws AbortIndexingException
     */
    protected function processFiles(Finder $finder) {
        $abortCheckCounter = 0;

        $this->checkForAbort();
        foreach ($finder as $file) {
            $this->indexFile($file);

            if ($abortCheckCounter === 100){
                //check for abort state very 100 indexed files
                $this->checkForAbort();
                $abortCheckCounter = 0;
            }
            $abortCheckCounter++;
        }
    }

    /**
     * Checks if the indexing process should be aborted (triggered by the user)
     *
     * @throws AbortIndexingException
     */
    protected function checkForAbort() {
        if (IndexState::isAborted()) {
            throw new AbortIndexingException;
        }
    }

    /**
     * Gets or creates the index status entry in the database
     */
    protected function initializeIndexState()
    {
        $this->indexState = IndexState::getCurrent();
    }

    /**
     * Returns a configured finder instance which searches for allowed files in the configured image directory
     */
    protected function configureFinder(): Finder
    {
        $finder = new Finder();
        $finder
            ->in(config('slideshow.imagePath'))
            ->name($this->indexFileExtensions)
            ->exclude($this->excludedDirectories)
            ->files()
        ;
        return $finder;
    }

    /**
     * Checks all index entries which were not updated during last index run if the files have been deleted
     * and removes the index entry from the database if so
     * @throws \Exception
     */
    protected function cleanUpIndex()
    {
        $notUpdated = Index::query()->where('last_indexed', '<', $this->indexTime)->get();
        /** @var Index $index */
        foreach ($notUpdated as $index) {
            $index->delete();
        }
    }

    /**
     * Adds a new index entry to the database
     */
    protected function indexFile(SplFileInfo $file)
    {
        Index::query()->updateOrCreate(
            [
                'path' => $file->getPath(),
                'file_name' => (string)$file->getFilename()
            ],
            [
                'year' =>  $this->extractYear($file),
                'month' => date('m', $file->getCTime()),
                'base_name' => $file->getPathInfo()->getBasename(),
                'last_indexed' => $this->indexTime
            ]
        );
        $this->indexState['current']+=1;
        $this->indexState->save();
    }

    /**
     * Get the year for the file (If a folder name traversed upwards in the file path matches a year use this or
     * returns the year of the creation date of the file)
     */
    protected function extractYear(SplFileInfo $file): int
    {
        $directoryPath = $file->getRealPath();
        do {
            //traverse folders up and look for dir names which look like a year. If one can be found, return that year
            $directoryPath = dirname($directoryPath);
            $potentialYear = basename($directoryPath);

            if (preg_match('/^[0-9]{4}$/', $potentialYear)) {
                return (int)$potentialYear;
            }
        } while ($directoryPath !== '/');

        //if no such folder could be found, just return the year of the creation date of the file
        return date('Y', $file->getCTime());
    }
}
