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

namespace XLite\Model\Base;

/**
 * Name-value abstract storage
 *
 * @MappedSuperclass
 */
abstract class NameValue extends \XLite\Model\AEntity
{

    /**
     * Unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Parameter name 
     * 
     * @var string
     *
     * @Column (type="string")
     */
    protected $name;

    /**
     * Semi-serialized parameter value representation
     * 
     * @var string
     *
     * @Column (type="text")
     */
    protected $value;

    /**
     * Get parameter value
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = @unserialize($this->value);

        return false === $value ? $this->value : $value;
    }

    /**
     * Set parameter value
     *
     * @param mixed $value Parameter value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = is_scalar($value) ? $value : serialize($value);
    }
}
