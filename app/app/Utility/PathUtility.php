<?php
namespace App\Utility;

class PathUtility
{
    public static function getPublicPath($path){
        return str_replace(config('slideshow.imagePath'), '/images', $path);
    }
}
