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

namespace XLite\Module\XC\Upselling\Model;

/**
 * Upselling Product
 *
 * @Entity
 * @Table (name="upselling_products",
 *      indexes={
 *          @Index (name="parent_product_index", columns={"parent_product_id"}),
 *      }
 * )
 */
class UpsellingProduct extends \XLite\Model\AEntity
{
    /**
     * Session cell name
     */
    const SESSION_CELL_NAME = 'upsellingProductsSearch';

    /**
     * Unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $id;

    /**
     * Sort position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $orderBy = 0;

    /**
     * Product (relation)
     *
     * @var \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="upsellingProducts")
     * @JoinColumn (name="product_id", referencedColumnName="product_id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * Parent product (relation)
     *
     * @var \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="upsellingParentProducts")
     * @JoinColumn (name="parent_product_id", referencedColumnName="product_id", onDelete="CASCADE")
     */
    protected $parentProduct;


    /**
     * SKU getter
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * Price getter
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->getProduct()->getPrice();
    }

    /**
     * Check if the bi-directional link is needed
     *
     * @return boolean
     */
    public function getBidirectional()
    {
        $linkData = array(
            'parentProduct' => $this->getProduct(),
            'product'       => $this->getParentProduct(),
        );

        return (bool)$this->getRepository()->findOneBy($linkData);
    }

    /**
     * Check if the bi-directional link is needed
     *
     * @return boolean
     */
    public function setBidirectional($newValue)
    {
        $newValue
            ? $this->getRepository()->addBidirectionalLink($this)
            : $this->getRepository()->deleteBidirectionalLink($this);
    }

    /**
     * Amount getter
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->getProduct()->getInventory()->getPublicAmount();
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->getOrderBy();
    }

    /**
     * Set position
     *
     * @param integer $position Upselling link position
     *
     * @return void
     */
    public function setPosition($position)
    {
        return $this->setOrderBy($position);
    }
}
