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

namespace XLite\Module\XC\RESTAPI\Core\Printer;

/**
 * XML printer
 */
class XML extends \XLite\Module\XC\RESTAPI\Core\Printer\Base\HTTP
{
    /**
     * Check - schema is own this request or not
     * 
     * @return boolean
     */
    public static function isOwn()
    {
        return !empty($_SERVER['HTTP_ACCEPT'])
            && 'application/xml' == $_SERVER['HTTP_ACCEPT']
            && empty(\XLite\Core\Request::getInstance()->callback);
    }

    /**
     * Print output
     *
     * @param mixed $data Data
     *
     * @return void
     */
    public function printOutput($data)
    {
        header('Content-Type: application/xml;charset=utf-8');

        parent::printOutput($data);
    }

    /**
     * Format output
     *
     * @param mixed $data Data
     *
     * @return mixed
     */
    protected function formatOutput($data)
    {
        $result = '<' . '?xml version="1.1" encoding="UTF-8" ?' . '>';

        $result .= is_array($data)
            ? $this->convertToXMLArray($data)
            : $this->convertToXMLCell('body', $data);

        return $result;
    }

    /**
     * Convert to XML array
     *
     * @param array $data Data
     *
     * @return string
     */
    protected function convertToXMLArray(array $data)
    {
        $result = '';

        foreach ($data as $name => $value) {
            $result .= $this->convertToXMLCell($name, $value);
        }

        return $result;
    }

    /**
     * Convert to XML cell
     *
     * @param string $name  Cell name
     * @param mixed  $value Cell value
     *
     * @return string
     */
    protected function convertToXMLCell($name, $value)
    {
        $type = gettype($value);
        $result = '<' . $name . ' type="' . $type .'">';

        if (is_scalar($value)) {

            switch ($type) {
                case 'boolean':
                    $result .= $value ? 'true' : 'false';
                    break;

                case 'integer':
                case 'double':
                    $result .= $value;
                    break;

                case 'string':
                    $result .= htmlspecialchars($value, \ENT_XML1);
                    break;

                default:
            }

        } elseif (is_array($value)) {
            $result .= $this->convertToXMLArray($value);
        }

        return $result . '</' . $name . '>';
    }

}
