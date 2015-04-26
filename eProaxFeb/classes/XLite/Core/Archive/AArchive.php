<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Core\Archive;

/**
 * Abstract archiver
 */
abstract class AArchive extends \XLite\Base
{
    /**
     * Pack files
     *
     * @param array  $files            Files
     * @param string &$destinationPath Destination path
     *
     * @return boolean
     */
    abstract public function pack(array $files, &$destinationPath);

    /**
     * Unpack archive
     *
     * @param string  $path            Archive path
     * @param string  $destinationPath Destination path
     * @param boolean $safeMode        Safe mode OPTIONAL
     *
     * @return boolean
     */
    abstract public function unpack($path, $destinationPath, $safeMode = false);

    /**
     * Get archive files listing
     *
     * @param string $path Archive path
     *
     * @return array
     */
    abstract public function getList($path);

    /**
     * Check - can upack specified file
     *
     * @param string $path Path
     *
     * @return boolean
     */
    abstract public function canUpackFile($path);

    /**
     * Get archiver code
     *
     * @return string
     */
    abstract public function getCode();

    /**
     * Check - archiver is valid or not
     *
     * @return boolean
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Checking file is safe or not
     *
     * @param string $path Path
     *
     * @return boolean
     */
    public function isSafeFile($path)
    {
        $pathinfo = pathinfo($path);

        return '.htaccess' != $pathinfo['basename']
            && (
                empty($pathinfo['extension'])
                || 'php' != $pathinfo['extension']
            );
    }

    /**
     * Get common directory
     *
     * @param array $files Files list
     *
     * @return string
     */
    protected function getCommonDirectory(array $files)
    {
        $common = dirname(array_shift($files));
        $length = strlen($common);
        do {
            $found = true;
            foreach ($files as $path) {
                if (0 !== strpos($path, $common)) {
                    $found = false;
                }
            }

            if (!$found) {
                if ($common == dirname($common)) {
                    $common = null;
                    $length = 0;

                } else {
                    $common = dirname($common);
                    $length = strlen($common);
                }
            }

        } while (!$found && 0 < $length);

        return 0 < $length ? $common : null;
    }
}
