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
 * Product
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Links to related products (relation [this product] -> [related product])
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Module\XC\Upselling\Model\UpsellingProduct", mappedBy="parentProduct", cascade={"all"})
     */
    protected $upsellingProducts;

    /**
     * Back links from related products (back relation [related product] -> [this product])
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Module\XC\Upselling\Model\UpsellingProduct", mappedBy="product", cascade={"all"})
     */
    protected $upsellingParentProducts;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->upsellingProducts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->upsellingParentProducts = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $newProduct = parent::cloneEntity();

        if ($this->getUpsellingProducts()) {
            $this->cloneUpsellingLinks($newProduct, false);
        }

        if ($this->getUpsellingParentProducts()) {
            $this->cloneUpsellingLinks($newProduct, true);
        }

        return $newProduct;
    }

    /**
     * Clone upselling links
     *
     * @param \XLite\Model\Product $product   Cloned product object
     * @param boolean              $backLinks Flag: true - create back links, false - direct links
     *
     * @return void
     */
    protected function cloneUpsellingLinks($product, $backLinks = false)
    {
        $upsellingLinks = $backLinks
            ? $this->getUpsellingParentProducts()
            : $this->getUpsellingProducts();

        foreach ($upsellingLinks as $up) {

            $newUp = new \XLite\Module\XC\Upselling\Model\UpsellingProduct();

            if ($backLinks) {
                $newUp->setProduct($product);
                $newUp->setParentProduct($up->getParentProduct());

            } else {
                $newUp->setProduct($up->getProduct());
                $newUp->setParentProduct($product);
            }

            $newUp->setOrderBy($up->getOrderBy());

            \XLite\Core\Database::getEM()->persist($newUp);
        }
    }
}
