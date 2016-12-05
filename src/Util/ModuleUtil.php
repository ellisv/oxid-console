<?php

namespace Ellis\Oxid\Console\Util;

class ModuleUtil
{
    /**
     * Does a module id exist in a system.
     *
     * @param string $id
     *
     * @return bool
     */
    public static function exists($id)
    {
        return array_key_exists($id, \oxRegistry::getConfig()->getConfigParam('aModulePaths'));
    }

    /**
     * Check whenever a directory path is not already taken for given vendor
     * and a name.
     *
     * @param string $vendor
     * @param string $name
     *
     * @return bool
     */
    public static function isPathAvailable($vendor, $name)
    {
        return !is_dir(self::buildDirectoryPath($vendor, $name));
    }

    /**
     * Build a directory path by given vendor and a name.
     *
     * @param string $vendor
     * @param string $name
     *
     * @return string
     */
    public static function buildDirectoryPath($vendor, $name)
    {
        return array_reduce(array(trim($vendor), trim($name)), function ($path, $token) {
            return $token ? PathUtil::join($path, strtolower($token)) : $path;
        }, PathUtil::join(OX_BASE_PATH, 'modules'));
    }
}
