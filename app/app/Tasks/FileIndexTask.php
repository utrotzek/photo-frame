<?php


namespace App\Tasks;


use App\Models\IndexState;
use App\Models\Index;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileIndexTask
{
    protected $imageRootDirectory = '/var/images';
    protected $indexFileExtensions = ['*.JPG', '*.jpg', '*.JPEG', '*.png', '*.PNG'];

    protected $maxRetries = 7;

    protected IndexState $indexState;

    public function updateIndex()
    {
        $this->initializeIndexState();

        try {
            if (
                $this->indexState['state'] ===  IndexState::STATE_WAITING ||
                ($this->indexState['state'] ===  IndexState::STATE_FAILED && $this->indexState['retries'] < $this->maxRetries)
            ) {
                $this->updateIndexComplete();
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
        $indexState['state'] = IndexState::STATE_WAITING;
        $this->indexState = $indexState;
        $this->indexState->save();
        $this->indexState->fresh();
    }

    /**
     * Finds all files in the image directory and updates the index entry
     */
    protected function updateIndexComplete()
    {
        $finder = new Finder();
        $finder
            ->in($this->imageRootDirectory)
            ->name($this->indexFileExtensions)
            ->files()
        ;

        $this->indexState->setWorking($finder->count());

        foreach ($finder as $file) {
            $this->indexFile($file);
            $this->indexState['current']+=1;
            $this->indexState->save();
        }
    }

    /**
     * Checks all index entries which were not updated during last index run if the files have been deleted
     * and removes the index entry from the database if so
     * @throws \Exception
     */
    protected function cleanUpIndex()
    {
        $notUpdated = Index::query()->where('last_indexed', '<', $this->indexState['last_run'])->get();
        /** @var Index $index */
        foreach ($notUpdated as $index) {
            if (!file_exists($index['full_path'])) {
                $index->delete();
            }
        }
    }

    /**
     * Adds a new index entry to the database
     *
     * @param SplFileInfo $file
     */
    protected function indexFile(SplFileInfo $file)
    {
        Index::query()->updateOrCreate(
            ['full_path' => $file->getRealPath()],
            [
                'file_name' => (string)$file->getFilename(),
                'year' =>  $this->extractYear($file),
                'month' => date('m', $file->getCTime()),
                'base_name' => $file->getPathInfo()->getBasename(),
                'last_indexed' => $this->indexState['last_run']
            ]
        );
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
