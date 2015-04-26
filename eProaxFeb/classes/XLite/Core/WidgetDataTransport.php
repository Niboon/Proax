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
 * Widget data transport
 */
class WidgetDataTransport extends \XLite\Base
{
    /**
     * Handler to use
     *
     * @var \XLite\View\AView|null
     */
    protected $handler;


    /**
     * Save passed handler
     *
     * @param \XLite\View\AView|null $handler Passed handler
     *
     * @return void
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Get widget
     *
     * @return \XLite\View\AView
     */
    public function getProtectedWidget()
    {
        return $this->handler;
    }

    /**
     * Call handler methods
     *
     * @param string $method Method to call
     * @param array  $args   Call arguments OPTIONAL
     *
     * @return mixed
     */
    public function __call($method, array $args = array())
    {
        return isset($this->handler) ? call_user_func_array(array($this->handler, $method), $args) : null;
    }
}
