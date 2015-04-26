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

namespace XLite\Module\XC\ProductVariants\View\Product;

/**
 * Attributes
 *
 * @ListChild (list="admin.product.variants", zone="admin", weight="10")
 */
class Attributes extends \XLite\Module\XC\ProductVariants\View\Product\AProduct
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/script.js';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return parent::getDir() . '/attributes';
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getMultipleAttributes();
    }

    /**
     * Return attribute title
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     *
     * @return integer
     */
    protected function getAttributeCount(\XLite\Model\Attribute $attribute)
    {
        return count($attribute->getAttributeValue($this->getProduct()));
    }

    /**
     * Return attribute title
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     *
     * @return string
     */
    protected function getAttributeTitle(\XLite\Model\Attribute $attribute)
    {
        return static::t(
            '{{count}} options',
            array(
                'count' => $this->getAttributeCount($attribute)
            )
        );

    }

    /**
     * Attribute is checked flag
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     *
     * @return boolean
     */
    protected function isChecked(\XLite\Model\Attribute $attribute)
    {
        return in_array($attribute->getId(), $this->getVariantsAttributeIds());
    }

    /**
     * Return block style
     *
     * @return string
     */
    protected function getBlockStyle()
    {
        $style = parent::getBlockStyle() . ' attributes';

        if ($this->isAllowVaraintAdd()) {
            $style .= ' checked';
        }

        if ($this->getVariantsAttributes()) {
            $style .= ' hidden';
        }

        return $style;
    }
}
