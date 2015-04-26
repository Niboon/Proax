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

namespace XLite\Model\Order;

/**
 * Order modifier
 *
 * @Entity
 * @Table (name="order_modifiers")
 */
class Modifier extends \XLite\Model\AEntity
{
    /**
     * ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Logic class name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $class;

    /**
     * Weight
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $weight = 0;

    /**
     * Modifier object (cache)
     *
     * @var \XLite\Logic\Order\Modifier\AModifier
     */
    protected $modifier;

    /**
     * Magic call
     *
     * @param string $method Method name
     * @param array  $args   Arguments list OPTIONAL
     *
     * @return mixed
     */
    public function __call($method, array $args = array())
    {
        $modifier = $this->getModifier();

        return ($modifier && method_exists($modifier, $method))
            ? call_user_func_array(array($modifier, $method), $args)
            : parent::__call($method, $args);
    }

    /**
     * Get modifier object
     *
     * @return \XLite\Logic\Order\Modifier\AModifier
     */
    public function getModifier()
    {
        if (!isset($this->modifier) && \XLite\Core\Operator::isClassExists($this->getClass())) {
            $class = $this->getClass();
            $this->modifier = new $class($this);
        }

        return $this->modifier;
    }
}
