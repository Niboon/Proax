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

namespace XLite\Core\Archive\Base;

/**
 * Tar-based archiver
 */
abstract class Tar extends \XLite\Core\Archive\AArchive
{
    /**
     * Prepare destination path
     *
     * @param string &$destinationPath Destination path
     *
     * @return void
     */
    abstract protected function prepareDestinationPath(&$destinationPath);

    /**
     * Create packer
     *
     * @param string $destinationPath Destination path
     *
     * @return \Archive_Tar
     */
    abstract protected function createPacker($destinationPath);

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
        require_once (LC_DIR_LIB . 'Archive/Tar.php');

        $this->prepareDestinationPath($destinationPath);

        $packer = $this->createPacker($destinationPath);

        $result = false;
        $oldDir = getcwd();
        $newDir = $this->getCommonDirectory($files);
        if ($newDir) {
            chdir($newDir);

            $len = strlen($newDir) + 1;
            foreach ($files as $i => $lpath) {
                if (is_file($lpath)) {
                    $files[$i] = substr($lpath, $len);

                } else {
                    unset($files[$i]);
                }
            }

            $result = $packer->create(array_values($files));

            chdir($oldDir);
        }

        return $result;
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
        require_once (LC_DIR_LIB . 'Archive/Tar.php');

        $packer = $this->createPacker($path);
        $result = false;

        if ($safeMode) {
            $entries = array();
            $files = $packer->listContent();
            if ($files) {
                foreach ($files as $file) {
                    if (
                        '5' !== $file['typeflag'] // empty for file, "5" for directory
                        && $this->isSafeFile($file['filename'])
                    ) {
                        $entries[] = $file['filename'];
                    }
                }
            }

            if ($entries) {
                $result = $packer->extractList($entries, $destinationPath);
            }

        } else {
            $result = $packer->extract($destinationPath);
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
        require_once (LC_DIR_LIB . 'Archive/Tar.php');

        $packer = $this->createPacker($path);

        return $packer->listContent();
    }

}

