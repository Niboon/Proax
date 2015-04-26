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

namespace XLite\Module\XC\ProductVariants\Controller\Admin;

/**
 * Product variants page controller (Product modify section)
 */
class Product extends \XLite\Controller\Admin\Product implements \XLite\Base\IDecorator
{
    /**
     * Page key
     */
    const PAGE_VARIANTS = 'variants';

    /**
     * Multiple attributes
     *
     * @var array
     */
    protected $multipleAttributes;

    /**
     * Variants attribute ids
     *
     * @var array
     */
    protected $variantsAttributeIds;

    /**
     * Possible variants count
     *
     * @var integer
     */
    protected $possibleVariantsCount;

    /**
     * Get multiple attributes
     *
     * @return array
     */
    public function getMultipleAttributes()
    {
        if (!isset($this->multipleAttributes)) {
            $this->multipleAttributes = $this->getProduct()->getMultipleAttributes();
        }

        return $this->multipleAttributes;
    }

    /**
     * Get variants attribute ids
     *
     * @return array
     */
    public function getVariantsAttributeIds()
    {
        if (!isset($this->variantsAttributeIds)) {
            $this->variantsAttributeIds = $this->getProduct()->getVariantsAttributeIds();
        }

        return $this->variantsAttributeIds;
    }

    /**
     * Get variants attributes
     *
     * @return array
     */
    public function getVariantsAttributes()
    {
        return $this->getProduct()->getVariantsAttributes()->toArray();
    }

    /**
     * Get possible variants count
     *
     * @return integer
     */
    public function getPossibleVariantsCount()
    {
        if (!isset($this->possibleVariantsCount)) {
            $this->possibleVariantsCount = $this->getProduct()->getAllPossibleVariantsCount();
        }

        return $this->possibleVariantsCount;
    }

    /**
     * Check - add variant or no
     *
     * @return boolean
     */
    public function isAllowVaraintAdd()
    {
        return $this->getPossibleVariantsCount() > $this->getProduct()->getVariants()->count();
    }

    /**
     * Get pages
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();

        if (!$this->isNew()) {
            $list = array_merge(
                array_slice($list, 0, 2),
                array(static::PAGE_VARIANTS => static::t('Variants')),
                array_slice($list, 2)
            );
        }

        return $list;
    }

    /**
     * Get pages templates
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $list = parent::getPageTemplates();

        if (!$this->isNew()) {
            $list = array_merge(
                array_slice($list, 0, 2),
                array(static::PAGE_VARIANTS => 'modules/XC/ProductVariants/variants/body.tpl'),
                array_slice($list, 2)
            );
        }

        return $list;
    }

    /**
     * Update variants attributes
     *
     * @return void
     */
    protected function updateVariantsAttributes()
    {
        $attr = \XLite\Core\Request::getInstance()->attr;
        $product = $this->getProduct();

        $product->getVariantsAttributes()->clear();
        if ($attr) {
            $attributes = \XLite\Core\Database::getRepo('XLite\Model\Attribute')->findByIds($attr);
            foreach ($attributes as $a) {
                $product->addVariantsAttributes($a);
                $a->addVariantsProduct($product);
            }
        }

        $product->checkVariants();
        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Do create variants
     *
     * @return void
     */
    protected function doActionCreateVariants()
    {
        $this->updateVariantsAttributes();

        $product = $this->getProduct();

        $variants = array();
        foreach ($this->getVariantsAttributes() as $a) {
            $_variants = $variants;
            $variants = array();
            foreach ($a->getAttributeValue($this->getProduct()) as $attributeValue) {
                $val = array(array($attributeValue, $a->getType()));
                if ($_variants) {
                    foreach ($_variants as $v) {
                        $variants[] = array_merge($val, $v);
                    }
                } else {
                    $variants[] = $val;
                }
            }
        }

        if ($variants) {
            foreach ($variants as $attributeValues) {
                $variant = new \XLite\Module\XC\ProductVariants\Model\ProductVariant();
                foreach ($attributeValues as $attributeValue) {
                    $method = 'addAttributeValue' . $attributeValue[1];
                    $attributeValue = $attributeValue[0];
                    $variant->$method($attributeValue);
                    $attributeValue->addVariants($variant);
                }
                $variant->setProduct($product);
                $product->addVariants($variant);
                \XLite\Core\Database::getEM()->persist($variant);
            }
        }

        \XLite\Core\Database::getEM()->flush();

        $this->getProduct()->checkVariants();

        \XLite\Core\TopMessage::addInfo('Variants have been created successfully');
    }

    /**
     * Do discard variants
     *
     * @return void
     */
    protected function doActionDiscardVariants()
    {
        if ($this->getProduct()->getVariants()) {
            $repo = \XLite\Core\Database::getRepo('\XLite\Module\XC\ProductVariants\Model\ProductVariant');
            foreach ($this->getProduct()->getVariants() as $v) {
                $repo->delete($v, false);
            }
            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo('Variants have been discarded successfully');
        }
    }

    /**
     * Do update variants
     *
     * @return void
     */
    protected function doActionUpdateVariants()
    {
        $list = new \XLite\Module\XC\ProductVariants\View\ItemsList\Model\ProductVariant;
        $list->processQuick();
        $this->getProduct()->checkVariants();

        \XLite\Core\TopMessage::addInfo('Variants have been updated successfully');
    }

    /**
     * Do update variants attributes
     *
     * @return void
     */
    protected function doActionUpdateVariantsAttributes()
    {
        $this->updateVariantsAttributes();
    }

    /**
     * Do action delete
     *
     * @return void
     */
    protected function doActionDeleteVariants()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if (
            $select
            && is_array($select)
            && $this->getProduct()->getVariants()
        ) {
            $selectedKeys = array_keys($select);

            $repo = \XLite\Core\Database::getRepo('\XLite\Module\XC\ProductVariants\Model\ProductVariant');

            foreach ($this->getProduct()->getVariants() as $v) {
                if (in_array($v->getId(), $selectedKeys)) {
                    $repo->delete($v, false);
                }
            }

            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo('Variants have been successfully deleted');

        } else {
            \XLite\Core\TopMessage::addWarning('Please select the products first');
        }
    }
}
