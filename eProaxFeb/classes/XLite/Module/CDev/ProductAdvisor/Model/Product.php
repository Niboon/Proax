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

namespace XLite\Module\CDev\ProductAdvisor\Model;

/**
 * Product model extension
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Relation to product views statistics
     *
     * @var   \XLite\Module\CDev\ProductAdvisor\Model\ProductStats
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\ProductAdvisor\Model\ProductStats", mappedBy="viewed_product", fetch="LAZY")
     */
    protected $views_stats;

    /**
     * Relation to product purchase statistics
     *
     * @var   \XLite\Module\CDev\ProductAdvisor\Model\ProductStats
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\ProductAdvisor\Model\ProductStats", mappedBy="bought_product", fetch="LAZY")
     */
    protected $purchase_stats;


    /**
     * Returns true if product is classified as a new product
     * 
     * @return boolean
     */
    public function isNewProduct()
    {
        $currentDate = static::getUserTime();

        $daysOffset = \XLite\Module\CDev\ProductAdvisor\Main::getNewArrivalsOffset();

        return \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->na_enabled
            && $this->getArrivalDate() 
            && $this->getArrivalDate() < $currentDate 
            && $this->getArrivalDate() > $currentDate - 86400 * $daysOffset;
    }

    /**
     * Returns true if product is classified as an upcoming product
     * 
     * @return boolean
     */
    public function isUpcomingProduct()
    {
        $currentDate = static::getUserTime();

        return \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->cs_enabled
            && $this->getArrivalDate() 
            && $this->getArrivalDate() > $currentDate;
    }
}
