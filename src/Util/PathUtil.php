<?php

namespace Ellis\Oxid\Console\Util;

/**
 * Various utilities related to file system.
 */
class PathUtil
{
    /**
     * Concatinate given arguments with directory separators.
     *
     * Example:
     * PathUtil::join('something/', 'is', 'awesome') ==> 'something/is/awesome'
     *
     * @param string $path
     *
     * @return string
     */
    public static function join($path)
    {
        return self::joinArray(func_get_args());
    }

    /**
     * @param string[] $args
     *
     * @return string
     */
    private static function joinArray(array $args)
    {
        $path = $args[0];
        if (count($args) === 1) {
            return $path;
        }

        if (substr($path, -1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path . self::joinArray(array_slice($args, 1));
    }

    /**
     * Remove given directory except for given files to keep.
     *
     * @param string   $path
     * @param string[] $keep
     */
    public static function rmtree($path, $keep = array())
    {
        $glob = self::join($path, '*');

        foreach (glob($glob) as $filePath) {
            $fileName = basename($filePath);
            if (in_array($fileName, $keep)) {
                continue;
            }

            is_dir($filePath)
                ? self::rmdir($filePath)
                : unlink($filePath);
        }
    }

    /**
     * @param string $path
     */
    private static function rmdir($path)
    {
        if (!is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->getFilename() == '.' || $file->getFilename() === '..') {
                continue;
            }

            $file->isDir()
                ? rmdir($file->getRealPath())
                : unlink($file->getRealPath());
        }

        rmdir($path);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function resourcePath($fileName)
    {
        return self::join(dirname(dirname(__DIR__)), 'res', $fileName);
    }
}
