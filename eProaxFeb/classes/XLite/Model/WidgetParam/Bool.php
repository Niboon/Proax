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

namespace XLite\Model\WidgetParam;

/**
 * ____description____
 */
class Bool extends \XLite\Model\WidgetParam\Set
{
    /**
     * Values for TRUE value
     *
     * @var array
     */
    protected $trueValues = array('1', 'true', 1, true);

    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'true'  => 'Yes',
        'false' => 'No',
    );

    /**
     * Get value by name
     *
     * @param mixed $name Value to get
     *
     * @return boolean
     */
    public function __get($name)
    {
        return $this->isTrue(parent::__get($name));
    }


    /**
     * Find if it is true value
     *
     * @param mixed $value Value of widget parameter
     *
     * @return boolean
     */
    protected function isTrue($value)
    {
        return in_array($value, $this->trueValues, true);
    }
}
