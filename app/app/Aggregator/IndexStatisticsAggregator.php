<?php


namespace App\Aggregator;


use App\Models\Index;

class IndexStatisticsAggregator
{

    public static function overallStatistics(): array
    {
        return [
            'totalFiles' => Index::query()->count(),
            'newestFileDate' => Index::getFileDate(true),
            'oldestFileDate' => Index::getFileDate(false)
        ];
    }

    public static function favoritesCount(): int
    {
        return Index::where('favorite', true)->count();
    }
}
