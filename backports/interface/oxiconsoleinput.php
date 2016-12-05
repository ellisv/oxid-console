<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Input interface is implemented by all console input classes.
 *
 * @deprecated since version 1.3, to be removed in 2.0.
 *             Use Symfony\Component\Console\Input\InputInterface instead.
 */
interface oxIConsoleInput
{
    /**
     * Get first argument
     *
     * @return string|null
     */
    public function getFirstArgument();

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Returns all set arguments
     *
     * @return array
     */
    public function getArguments();

    /**
     * @param array|string $mOption In array it returns first found option
     *
     * @return mixed|null
     */
    public function getOption($mOption);

    /**
     * Has option
     *
     * @param array|string $mOption
     *
     * @return bool
     */
    public function hasOption($mOption);

    /**
     * Get argument at given offset
     *
     * @param integer $iOffset starts at 0
     *
     * @return mixed|null
     */
    public function getArgument($iOffset);

    /**
     * Prompt user for an input
     *
     * @param string $sTitle
     *
     * @return string
     */
    public function prompt($sTitle = null);
}
