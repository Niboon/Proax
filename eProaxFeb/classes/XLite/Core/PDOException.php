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

namespace XLite\Core;

/**
 * Extended PDO exception
 */
class PDOException extends \PDOException
{
    /**
     * Constructor
     *
     * @param \PDOException $e      PDO exception
     * @param string        $query  SQL query OPTIONAL
     * @param array         $params SQL query parameters OPTIONAL
     *
     * @return void
     */
    public function __construct(\PDOException $e, $query = null, array $params = array())
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        // Remove user credentials
        if (
            strstr($message, 'SQLSTATE[')
            && preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $message, $matches)
        ) {
            $code = 'HT000' == $matches[1] ? $matches[2] : $matches[1];
            $message = $matches[3];
        }

        // Add additional information
        if ($query) {
            $message .= PHP_EOL . 'SQL query: ' . $query;
        }

        if ($params) {
            $message .= PHP_EOL . 'SQL query parameters: ' . var_export($params, true);
        }

        $this->code = intval($code);
        $this->message = $message;
    }
}
