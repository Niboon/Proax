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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\RESTAPI\Core\Printer\Base;

/**
 * HTTP-based printer
 */
abstract class HTTP extends \XLite\Module\XC\RESTAPI\Core\Printer\APrinter
{

    /**
     * Last modified time
     * 
     * @var   integer
     */
    protected $lastModified;

    /**
     * Format output 
     * 
     * @param mixed $data Data
     *  
     * @return mixed
     */
    abstract protected function formatOutput($data);

    /**
     * Print output
     *
     * @param mixed $data Data
     *
     * @return void
     */
    public function printOutput($data)
    {
        header('Allow: GET,POST,PUT,DELETE');
        header('Date: ' . date('r', \XLite\Core\Converter::time()));
        header('X-Result-Count: ' . ($this->schema->getConfig()->one ? '1' : count($data)));

        $this->printRaw($this->formatOutput($data));
    }

    // {{{ Print raw data

    /**
     * Print raw data
     *
     * @param string $data Data
     *
     * @return void
     */
    protected function printRaw($data)
    {
        header('Accept-Ranges: bytes');

        $data = $this->processResponseRange($data);

        header('Last-Modified: ' . date('r', $this->getLastModified()));
        header('ETag: ' . $this->getETag($data));

        if (!$this->processAs304($data)) {
            header('Content-Length: ' . strlen($data));
            header('Content-MD5: ' . base64_encode(md5($data, true)));
            print ($data);
        }
    }

    /**
     * Process response range
     *
     * @param string $data Data
     *
     * @return string
     */
    protected function processResponseRange($data)
    {
        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(.+)$/Ss', $_SERVER['HTTP_RANGE'], $match)) {
            $min = null;
            $max = null;

            foreach (explode(',', $match[1]) as $range) {
                list($r1, $r2) = explode('-', $range);
                if (!$r1) {
                    $r1 = 0;
                }
                if (!$r2) {
                    $r2 = strlen($data) - 1;
                }

                if (!isset($min) || $r1 < $min) {
                    $min = $r1;
                }

                if (!isset($max) || $r2 > $max) {
                    $max = $r1;
                }
            }

            if (!isset($min)) {
                $min = 0;
            }

            if (!isset($max)) {
                $max = strlen($data) - 1;
            }

            $max = min($max, strlen($data) - 1);

            header('Content-Range: bytes=' . $min . '-' . $max, true, 206);
            $data = substr($data, $min, $max - $min + 1);
        }

        return $data;
    }

    /**
     * Get last modified time
     *
     * @return integer
     */
    protected function getLastModified()
    {
        if (!isset($this->lastModified)) {
            $this->lastModified = $this->defineLastModified();
        }

        return $this->lastModified;
    }

    /**
     * Define last modified time
     *
     * @return integer
     */
    protected function defineLastModified()
    {
        return \XLite\Core\Converter::time();
    }

    /**
     * Get data ETag
     *
     * @param string $data Data
     *
     * @return string
     */
    protected function getETag($data)
    {
        return md5($data);
    }

    /**
     * Process request as 304
     *
     * @param string $data Data
     *
     * @return boolean
     */
    protected function processAs304($data)
    {
        $result = false;

        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $result = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) > $this->getLastModified();
        }

        if (!$result && !empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
            $result = strtolower($_SERVER['HTTP_IF_NONE_MATCH']) == $this->getETag($data);
        }

        if ($result) {
            header('Status: 304 Not Modified', true, 304);
        }

        return $result;
    }

    // }}}

}
