<?php
namespace App\Utility;

class PathUtility
{
    public static function getPublicPath(string $path): string
    {
        return str_replace(config('slideshow.imagePath'), '/images', $path);
    }

    public static function getInternalPath(string $path): string
    {
        return str_replace('/images', config('slideshow.imagePath'), $path);
    }
}
