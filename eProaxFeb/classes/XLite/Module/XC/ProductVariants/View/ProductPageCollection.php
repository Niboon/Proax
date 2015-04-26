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

namespace XLite\Module\XC\ProductVariants\View;

/**
 * Product page widgets collection
 */
class ProductPageCollection extends \XLite\View\ProductPageCollection implements \XLite\Base\IDecorator
{
    /**
     * Register the view classes collection
     *
     * @return array
     */
    protected function defineWidgetsCollection()
    {
        $widgets = parent::defineWidgetsCollection();

        if ($this->getProduct()->hasVariants()) {
            $widgets = array_merge(
                $widgets,
                array(
                    '\XLite\View\Product\Details\Customer\CommonAttributes',
                    '\XLite\View\Product\Details\Customer\Stock',
                    '\XLite\View\Product\Details\Customer\Quantity',
                    '\XLite\View\Product\Details\Customer\AddButton',
                )
            );
        }

        return $widgets;
    }

    /**
     * Check - allowed display subwidget or not
     *
     * @param string $name Widget class name
     *
     * @return boolean
     */
    protected function isAllowedWidget($name)
    {
        $result = parent::isAllowedWidget($name);

        if ($result && $this->getProduct()->hasVariants()) {
            switch ($name) {
                case '\XLite\View\Product\Details\Customer\Quantity':
                case '\XLite\View\Product\Details\Customer\Stock':
                case '\XLite\View\Product\Details\Customer\AddButton':
                    $types = $this->getProductModifierTypes();
                    if (empty($types['quantity'])) {
                        $result = false;
                    }
                    break;

                case '\XLite\View\Product\Details\Customer\CommonAttributes':
                    $types = $this->getProductModifierTypes();
                    if (empty($types['weight']) && empty($types['sku'])) {
                        $result = false;
                    }
                    break;

                default:
            }
        }

        return $result;
    }

    /**
     * Get product modifier types
     *
     * @return array
     */
    protected function getProductModifierTypes()
    {
        $additional = null;
        if (!isset($this->productModifierTypes) && $this->getProduct()->hasVariants()) {
            $additional = \XLite\Core\Database::getRepo('XLite\Module\XC\ProductVariants\Model\ProductVariant')
                ->getModifierTypesByProduct($this->getProduct());
        }

        $result = parent::getProductModifierTypes();

        if (isset($additional)) {
            $result += $additional;
            if (!$result['price'] && $additional['price']) {
                $result['price'] = true;
            }
            if (!$result['weight'] && $additional['weight']) {
                $result['weight'] = true;
            }

            $this->productModifierTypes = $result;
        }

        return $result;
    }
}
