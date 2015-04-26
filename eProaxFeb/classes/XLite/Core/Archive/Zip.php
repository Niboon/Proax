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
 * Zip
 */
class Zip extends \XLite\Core\Archive\AArchive
{
    /**
     * Get archiver code
     *
     * @return string
     */
    public function getCode()
    {
        return 'zip';
    }

    /**
     * Check - archiver is valid or not
     *
     * @return boolean
     */
    public function isValid()
    {
        return parent::isValid() && class_exists('ZipArchive', false);
    }

    /**
     * Check - can upack specified file
     *
     * @param string $path Path
     *
     * @return boolean
     */
    public function canUpackFile($path)
    {
        return (bool)preg_match('/\.zip/Ss', $path);
    }

    /**
     * Pack files
     *
     * @param array  $files            Files
     * @param string &$destinationPath Destination path
     *
     * @return boolean
     */
    public function pack(array $files, &$destinationPath)
    {
        $destinationPath .= '.zip';

        $packer = new \ZipArchive;
        $packer->open($destinationPath, \ZipArchive::OVERWRITE);

        $commonDirectory = $this->getCommonDirectory($files);

        $len = strlen($commonDirectory) + 1;
        $dirs = array();
        foreach ($files as $file) {
            $localpath = substr($file, $len);
            if (is_file($file)) {
                $packer->addFile($file, $localpath);

            } elseif (!in_array($localpath, $dirs)) {
                $packer->addEmptyDir($localpath);
                $dirs[] = $localpath;
            }
        }
        $packer->close();

        return true;
    }

    /**
     * Unpack archive
     *
     * @param string  $path            Archive path
     * @param string  $destinationPath Destination path
     * @param boolean $safeMode        Safe mode OPTIONAL
     *
     * @return boolean
     */
    public function unpack($path, $destinationPath, $safeMode = false)
    {
        $result = false;

        $packer = new \ZipArchive;
        if ($packer->open($path)) {
            if ($safeMode) {
                $entries = array();

                for ($i = 0; $i < $packer->numFiles; $i++) {
                    $file = $packer->getNameIndex($i);
                    if ($this->isSafeFile($file)) {
                        $entries[] = $file;
                    }
                }

                if ($entries) {
                    $result = $packer->extractTo($destinationPath, $entries);
                }

            } else {
                $result = $packer->extractTo($destinationPath);
            }

            $packer->close();
        }

        return $result;
    }

    /**
     * Get archive files listing
     *
     * @param string $path Archive path
     *
     * @return array
     */
    public function getList($path)
    {
        $result = array();

        $packer = new \ZipArchive;
        if ($packer->open($path)) {
            for ($i = 0; $i < $packer->numFiles; $i++) {
                $result[] = $packer->getNameIndex($i);
            }
        }

        return $result;
    }

}

