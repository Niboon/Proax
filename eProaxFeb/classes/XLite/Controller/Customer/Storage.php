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

namespace XLite\Controller\Customer;

/**
 * Storage
 */
class Storage extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Storage 
     * 
     * @var \XLite\Model\Base\Storage
     */
    protected $storage;

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    protected function checkAccess()
    {
        return parent::checkAccess() && ('download' != $this->getAction() || $this->getStorage());
    }

    /**
     * Download
     *
     * @return void
     */
    protected function doActionDownload()
    {
        $this->silent = true;
        header('Content-Type: ' . $this->getStorage()->getMime());
        header('Content-Disposition: attachment; filename="' . addslashes($this->getStorage()->getFileName()) . '";');
        $this->readStorage($this->getStorage());
    }

    /**
     * Get storage 
     * 
     * @return \XLite\Model\Base\Storage
     */
    protected function getStorage()
    {
        if (
            !isset($this->storage)
            || !is_object($this->storage)
            || !($this->storage instanceof \XLite\Model\Base\Storage)
        ) {
            $class = \XLite\Core\Request::getInstance()->storage;
            if (\XLite\Core\Operator::isClassExists($class)) {
                $id = \XLite\Core\Request::getInstance()->id;
                $this->storage = \XLite\Core\Database::getRepo($class)->find($id);
                if (!$this->storage->isFileExists()) {
                    $this->storage = null;
                }
            }
        }

        return $this->storage;
    }

    /**
     * Read storage 
     * 
     * @param \XLite\Model\Base\Storage $storage Storage
     *  
     * @return void
     */
    protected function readStorage(\XLite\Model\Base\Storage $storage)
    {
        $range = null;

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($sizeUnit, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if ('bytes' == $sizeUnit) {
                list($range, $extra) = explode(',', $range, 2);
            }
        }

        $start = null;
        $length = $storage->getSize();

        if ($range) {
            $size = $length;
            list($start, $end) = explode('-', $range, 2);
            $start = abs(intval($start));
            $end = abs(intval($end));

            $end = $end ? min($end, $size - 1) : ($size - 1);
            $start = (!$start || $end < $start) ? 0 : max($start, 0);

            if (0 < $start || ($size - 1) > $end) {
                header('HTTP/1.2 206 Partial Content', true, 206);
            }

            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
            $length = ($end - $start + 1);
        }

        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $length);

        if (!\XLite\Core\Request::getInstance()->isHead()) {
            $storage->readOutput($start, $length);
        }
    }
}
