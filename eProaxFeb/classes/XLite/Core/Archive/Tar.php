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
 * Tar 
 */
class Tar extends \XLite\Core\Archive\Base\Tar
{
    /**
     * Get archiver code
     *
     * @return string
     */
    public function getCode()
    {
        return 'tar';
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
        return (bool)preg_match('/\.tar/Ss', $path);
    }

    /**
     * Prepare destination path
     *
     * @param string &$destinationPath Destination path
     *
     * @return string
     */
    protected function prepareDestinationPath(&$destinationPath)
    {
        $destinationPath .= '.tar';
    }

    /**
     * Create packer
     *
     * @param string $destinationPath Destination path
     *
     * @return \Archive_Tar
     */
    protected function createPacker($destinationPath)
    {
        return new \Archive_Tar($destinationPath);
    }

}

