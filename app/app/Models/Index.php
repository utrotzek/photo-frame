<?php

namespace App\Models;

use App\Utility\PathUtility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory;

    protected $table = 'index';

    protected $fillable = [
        'path',
        'file_name',
        'year' ,
        'month' ,
        'base_name',
        'last_indexed',
        'file_creation_date'
    ];

    protected $casts = [
        'last_indexed' => 'datetime',
        'file_creation_date' => 'datetime',
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function getFilePath(){
        return "{$this->path}/{$this->file_name}";
    }

    public function getPublicFilePath() {
        $publicPath = PathUtility::getPublicPath($this->path);
        return "{$publicPath}";
    }

    /**
     * Returns the newest or the oldest file date
     * @param bool $newest Whether to return the newest (true) or oldest file date (false)
     */
    public static function getFileDate($newest = true): \DateTime
    {
        $index = Index::query()
            ->orderBy('file_creation_date', ($newest) ? 'desc': 'asc')
            ->first();
        return $index['file_creation_date'];
    }

    /**
     * Returns a list of all years which are currently in the index
     * @return array
     */
    public static function getAllYears(): array
    {
        return self::query()
            ->select('year')
            ->groupBy('year')
            ->pluck('year')
            ->toArray();
    }
}
