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

    public static function getAlbumNameFromPath(string $path): string
    {
        if ((bool)config('slideshow.staticAlbumDepthEnabled')) {
            $depth = (int)config('slideshow.staticAlbumNameDepth');
            $trimmedPath = str_replace(config('slideshow.imagePath'), '', $path);
            $directoryArray = array_merge(array_filter(explode('/', $trimmedPath)));

            if (!isset($directoryArray[$depth])){
                return "Could not get album name";
            }
            $albumName = $directoryArray[$depth];
            return $albumName;
        } else {
            return basename($path);
        }
    }
}
