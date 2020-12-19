<?php


namespace App\Tasks;


use App\Models\IndexState;
use App\Models\Index;
use DateTime;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileIndexTask
{
    protected $imageRootDirectory = '/var/images';
    protected $indexFileExtensions = ['*.JPG', '*.jpg', '*.JPEG', '*.png', '*.PNG'];
    protected $excludedDirectories = ['Handy upload', 'unsortiert'];

    protected $maxRetries = 7;

    protected IndexState $indexState;
    protected DateTime $indexTime;

    /**
     * Does a full index update by not respecting any modification dates
     * @throws \Exception
     */
    public function completeIndexUpdate()
    {
        try {
            $this->initializeIndexState();
            $this->indexTime = new DateTime();
            $this->indexState['last_run'] = $this->indexTime;
            if (
                $this->indexState['state'] ===  IndexState::STATE_WAITING ||
                ($this->indexState['state'] ===  IndexState::STATE_FAILED && $this->indexState['retries'] < $this->maxRetries)
            ) {
                $finder = $this->getConfiguredFinder();
                $this->indexState->setWorking($finder->count());

                $this->updateIndexComplete($finder);
                $this->cleanUpIndex();

                $this->indexState->setFinished();
            }
        } catch (\Exception $exception) {
            $this->indexState['state'] = IndexState::STATE_FAILED;
            $this->indexState['message'] = $exception->getMessage();
            $this->indexState->save();
            throw new \Exception($exception);
        }
    }

    /**
     * Gets or creates the index status entry in the database
     */
    protected function initializeIndexState()
    {
        /** @var IndexState $indexState */
        $indexState = IndexState::query()->findOrNew(1);
        $this->indexState = $indexState;
        $this->indexState->save();
        $this->indexState->fresh();
    }

    /**
     * Finds all files in the image directory and updates the index entry
     */
    protected function updateIndexComplete(Finder $finder)
    {
        foreach ($finder as $file) {
            $this->indexFile($file);
        }
    }

    protected function getConfiguredFinder(): Finder
    {
        $finder = new Finder();
        $finder
            ->in($this->imageRootDirectory)
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
            if (!file_exists($index->getFilePath())) {
                $index->delete();
            }
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
     * Get the year for the file (If found a folder name in the path, otherwise the year of the creation date)
     */
    protected function extractYear(SplFileInfo $file): int
    {
        $directoryPath = $file->getRealPath();
        do {
            //move folders up and look for names which look like year names
            //if one is found return that year
            $directoryPath = dirname($directoryPath);
            $potentialYear = basename($directoryPath);

            if (preg_match('/^[0-9]{4}$/', $potentialYear)) {
                return (int)$potentialYear;
            }
        }while ($directoryPath !== '/');

        //if no such folder could be found, just return the year of the creation date of the file
        return date('Y', $file->getCTime());
    }
}
