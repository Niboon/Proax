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
 * Order item related object interface
 */
interface IOrderItem
{
    /**
     * Get unique id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight();

    /**
     * Get purchase limit (minimum)
     *
     * @return integer
     */
    public function getMinPurchaseLimit();

    /**
     * Get purchase limit (maximum)
     *
     * @return integer
     */
    public function getMaxPurchaseLimit();

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Get image
     *
     * @return \XLite\Model\Base\Image|void
     */
    public function getImage();

    /**
     * Get free shipping
     *
     * @return boolean
     */
    public function getFreeShipping();

    /**
     * Get URL
     *
     * @return string
     */
    public function getURL();
}
