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

namespace XLite\Module\XC\ProductVariants\Controller\Customer;

/**
 * Change attribute values from cart / wishlist item
 */
class ChangeAttributeValues extends \XLite\Controller\Customer\ChangeAttributeValues implements \XLite\Base\IDecorator
{
    /**
     * Error message
     *
     * @var string
     */
    protected $errorMessage = null;

    /**
     * Change product attribute values
     *
     * @param array $attributeValues Attrbiute values (prepared, from request)
     *
     * @return boolean
     */
    protected function saveAttributeValues(array $attributeValues)
    {
        $result = true;

        if ($this->getItem()->getProduct()->mustHaveVariants()) {
            $variant = $this->getItem()->getProduct()->getVariantByAttributeValues($attributeValues);

            if ($variant && 0 < $variant->getAvailableAmount()) {
                $this->getItem()->setVariant($variant);

            } else {
                $result = false;
                $this->errorMessage = static::t(
                    'Product with selected attribute value(s) is not available or out of stock. Please select other.'
                );

            }
        }

        return $result && parent::saveAttributeValues($attributeValues);
    }

    /**
     * Get error message
     *
     * @return string
     */
    protected function getErrorMessage()
    {
        return $this->errorMessage ?: parent::getErrorMessage();
    }
}
