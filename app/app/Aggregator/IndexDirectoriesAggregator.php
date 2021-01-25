<?php
namespace App\Aggregator;

use App\Models\Index;
use App\Utility\PathUtility;

class IndexDirectoriesAggregator
{

    public static function getIndexedDirectories(): array
    {
        $indexedPaths = Index::query()
            ->groupBy('path')
            ->orderBy('path')
            ->pluck('path');

        $tree = self::buildTree($indexedPaths);
        $tree['images']['nodes'] = self::convertToKeyBasedArray($tree['images']['nodes']);
        return $tree['images'];
    }

    /**
     * For easier access in the javascript frontend the tree array will be converted to a key based array instead
     * of an associative array. Associative array will be converted to "objects" in json later on which are unhandy
     * to iterate.
     *
     * @param $nodes
     * @return array
     */
    protected static function convertToKeyBasedArray($nodes){
        $nodes = array_values($nodes);

        foreach ($nodes as &$node){
            if (isset($node['nodes'])){
                $node['nodes'] = self::convertToKeyBasedArray($node['nodes']);
            }
        }
        return $nodes;
    }

    protected static function buildTree($pathArray) {
        $tree = array();
        foreach ($pathArray as $path){
            $path = PathUtility::getPublicPath($path);
            $currentTree = &$tree;
            $pathToken = strtok($path, '/');
            $subPath = '';

            while (($nextToken = strtok('/')) !== false) {
                $subPath = $subPath.'/'.$pathToken;
                if (!isset($currentTree[$pathToken])) {
                    $currentTree[$pathToken] = [
                        'title' => $pathToken,
                        'path' => $subPath,
                        'selected' => false
                    ];
                }
                $currentTree = &$currentTree[$pathToken]['nodes'];
                $pathToken = $nextToken;
            }
            $subPath = $subPath.'/'.$pathToken;
            $currentTree[$pathToken] = [
                'title' => $pathToken,
                'path' => $subPath,
                'selected' => false
            ];
            unset($currentTree);
        }

        return $tree;
    }


    protected static function getExplodedPath($path){
        return array_filter(
            explode('/',$path),
            function ($item) {return !empty($item);}
        );
    }
}
