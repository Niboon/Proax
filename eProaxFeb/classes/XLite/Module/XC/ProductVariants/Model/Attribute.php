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

namespace XLite\Module\XC\ProductVariants\Model;

/**
 * Attribute
 *
 */
class Attribute extends \XLite\Model\Attribute implements \XLite\Base\IDecorator
{
    /**
     * Variants products
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany (targetEntity="XLite\Model\Product", mappedBy="variantsAttributes", cascade={"merge","detach"})
     */
    protected $variantsProducts;

    /**
     * Get attribute value
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return mixed
     */
    public function getDefaultAttributeValue(\XLite\Model\Product $product)
    {
        $attributeValue = null;
        if ($product->mustHaveVariants()) {
            $variant = $product->getDefaultVariant();
            if ($variant) {
                foreach ($variant->getValues() as $av) {
                    if ($av->getAttribute()->getId() == $this->getId()) {
                        $attributeValue = $av;
                        break;
                    }
                }
            }
        }

        return $attributeValue ?: parent::getDefaultAttributeValue($product);
    }

    /**
     * Check attribute is variable or not
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return boolean
     */
    public function isVariable(\XLite\Model\Product $product)
    {
        $result = false;

        foreach ($product->getVariantsAttributes() as $a) {
            if ($a->getId() == $this->getId()) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
