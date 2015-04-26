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

namespace XLite\Model;

/**
 * DB-based configuration registry
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Config")
 * @Table  (name="config",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="nc", columns={"name", "category"})
 *      },
 *      indexes={
 *          @Index (name="orderby", columns={"orderby"}),
 *          @Index (name="type", columns={"type"})
 *      }
 * )
 */
class Config extends \XLite\Model\Base\I18n
{
    /**
     * Name for the Shipping category options
     */
    const SHIPPING_CATEGORY = 'Shipping';

    /**
     * Prefix for the shipping values
     */
    const SHIPPING_VALUES_PREFIX = 'anonymous_';

    /**
     * Option unique name
     *
     * @var string
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column (type="integer")
     */
    protected $config_id;

    /**
     * Option name
     *
     * @var string
     *
     * @Column (type="string", length=32)
     */
    protected $name;

    /**
     * Option category
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $category;

    /**
     * Option type
     * Allowed values:'','text','textarea','checkbox','country','state','select','serialized','separator'
     *     or forrm field class name
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $type = '';

    /**
     * Option position within category
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $orderby = 0;

    /**
     * Option value
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $value = '';

    /**
     * Widget parameters
     *
     * @var array
     *
     * @Column (type="array", nullable=true)
     */
    protected $widgetParameters;

}
