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

namespace XLite\Module\XC\SystemFields\Model;

/**
 * Product variant
 *
 * @LC_Dependencies("XC\ProductVariants")
 */
class ProductVariant extends \XLite\Module\XC\ProductVariants\Model\ProductVariant implements \XLite\Base\IDecorator
{
    /**
     * Product UPC/ISBN
     *
     * @var string
     *
     * @Column (type="string", length=32, nullable=true)
     */
    protected $upcIsbn;

    /**
     * Product Mnf#/Vendor#
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $mnfVendor = '';

    /**
     * Get display UPC/ISBN
     *
     * @return float
     */
    public function getDisplayUpcIsbn()
    {
        return $this->getUpcIsbn() ? : $this->getProduct()->getUpcIsbn();
    }

    /**
     * Get display Mnf#/Vendor#
     *
     * @return float
     */
    public function getDisplayMnfVendor()
    {
        return $this->getMnfVendor() ? : $this->getProduct()->getMnfVendor();
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $entity = parent::cloneEntity();

        $entity->setUpcIsbn(null);

        return $entity;
    }

    /**
     * Set upcIsbn
     *
     * @param string $upcIsbn field value
     *
     * @return Product
     */
    public function setUpcIsbn($upcIsbn)
    {
        $this->upcIsbn = \XLite\Core\Converter::isEmptyString($upcIsbn) ? null : $upcIsbn;

        return $this;
    }
}
