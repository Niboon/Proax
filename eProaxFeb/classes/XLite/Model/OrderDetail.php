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
 * Order details
 *
 * @Entity
 * @Table (name="order_details",
 *      indexes={
 *          @Index (name="oname", columns={"order_id","name"})
 *      }
 * )
 */
class OrderDetail extends \XLite\Model\AEntity
{
    /**
     * Order detail unique id
     *
     * @var mixed
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $detail_id;

    /**
     * Record name (code)
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $name;

    /**
     * Record label
     *
     * @var string
     *
     * @Column (type="string", length=255, nullable=true)
     */
    protected $label;

    /**
     * Value
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $value;

    /**
     * Relation to a order entity
     *
     * @var \XLite\Model\Order
     *
     * @ManyToOne  (targetEntity="XLite\Model\Order", inversedBy="details", fetch="LAZY")
     * @JoinColumn (name="order_id", referencedColumnName="order_id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * Get display record name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getLabel() ?: $this->getName();
    }
}
