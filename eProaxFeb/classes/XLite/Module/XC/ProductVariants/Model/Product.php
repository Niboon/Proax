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
 * Product
 *
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Product variants
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Module\XC\ProductVariants\Model\ProductVariant", mappedBy="product", cascade={"all"})
     */
    protected $variants;

    /**
     * Product variants attributes
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany (targetEntity="XLite\Model\Attribute", inversedBy="variantsProducts")
     * @JoinTable (
     *      name="product_variants_attributes",
     *      joinColumns={@JoinColumn (name="product_id", referencedColumnName="product_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="attribute_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $variantsAttributes;

    /**
     * Default variant
     *
     * @var   \XLite\Module\XC\ProductVariants\Model\ProductVariant
     */
    protected $defaultVariant;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->variants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->variantsAttributes = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get variant by attribute values ids
     *
     * @param array   $id            Ids
     * @param boolean $singleVariant Return single variants (true) or array of all matched variants (false) OPTIONAL
     *
     * @return mixed
     */
    public function getVariantByAttributeValuesIds(array $ids, $singleVariant = true)
    {
        $result = $singleVariant ? null : array();

        foreach ($this->getVariants() as $variant) {
            $match = true;
            foreach ($variant->getValues() as $v) {
                $match = isset($ids[$v->getAttribute()->getId()])
                    && $ids[$v->getAttribute()->getId()] == $v->getId();
                if (!$match) {
                    break;
                }
            }
            if ($match) {
                if ($singleVariant) {
                    $result = $variant;
                    break;

                } else {
                    $result[] = $variant;
                }
            }
        }

        return $result;
    }

    /**
     * Get variant by attribute values
     *
     * @param mixied $attributeValues Attribute values
     *
     * @return mixed
     */
    public function getVariantByAttributeValues($attributeValues)
    {
        $ids = array();

        if (is_array($ids)) {
            $tmp = $attributeValues;
            $tmp = array_shift($tmp);
            if (is_int($tmp)) {
                $ids = $attributeValues;
            }
        }

        if (!$ids) {
            foreach ($attributeValues as $av) {
                if (is_object($av)) {
                    $ids[$av->getAttribute()->getId()] = $av->getId();
                }
            }
        }

        return $this->getVariantByAttributeValuesIds($ids);
    }

    /**
     * Get default variant
     *
     * @return mixed
     */
    public function getDefaultVariant()
    {
        if (!isset($this->defaultVariant) || \XLite::isAdminZone()) {
            $this->defaultVariant = $this->defineDefaultVariant() ?: false;
        }

        return $this->defaultVariant ?: null;
    }

    /**
     * Get image
     *
     * @return \XLite\Model\Image\Product\Image
     */
    public function getImage()
    {
        return (!\XLite::isAdminZone() && $this->getDefaultVariant() && $this->getDefaultVariant()->getImage())
            ? $this->getDefaultVariant()->getImage()
            : parent::getImage();
    }

    /**
     * Get public images
     *
     * @return array
     */
    public function getPublicImages()
    {
        $list = parent::getPublicImages();

        if (
            !\XLite::isAdminZone()
            && $this->getDefaultVariant()
            && $this->getDefaultVariant()->getImage()
        ) {
            array_unshift($list, $this->getDefaultVariant()->getImage());
        }

        return $list;
    }

    /**
     * Define default variant
     *
     * @return \XLite\Module\XC\ProductVariants\Model\ProductVariant
     */
    protected function defineDefaultVariant()
    {
        $defVariant = null;

        if ($this->mustHaveVariants() && $this->hasVariants()) {
            $defVariant = \XLite\Core\Database::getRepo('\XLite\Module\XC\ProductVariants\Model\ProductVariant')->findOneBy(
                array(
                   'product'      => $this,
                   'defaultValue' => true,
                )
            );

            if (!$defVariant) {
                $minPrice = $minPriceOutOfStock = false;
                $defVariantOutOfStock = null;
                foreach ($this->getVariants() as $variant) {
                    if (!$variant->isOutOfStock()) {
                        if (false === $minPrice || $minPrice > $variant->getClearPrice()) {
                            $minPrice = $variant->getClearPrice();
                            $defVariant = $variant;
                        }
                    } elseif (!$defVariant) {
                        if (false === $minPriceOutOfStock || $minPriceOutOfStock > $variant->getClearPrice()) {
                            $minPriceOutOfStock = $variant->getClearPrice();
                            $defVariantOutOfStock = $variant;
                        }
                    }
                }
                $defVariant = $defVariant ?: $defVariantOutOfStock;
            }
        }

        return $defVariant;
    }

    /**
     * Get clear price
     *
     * @return float
     */
    public function getClearPrice()
    {
        return $this->getDefaultVariant()
            ? $this->getDefaultVariant()->getClearPrice()
            : parent::getClearPrice();
    }


    /**
     * Get variant
     *
     * @param mixied $attributeValues Attribute values OPTIONAL
     *
     * @return mixed
     */
    public function getVariant($attributeValues = null)
    {
        return $attributeValues
            ? $this->getVariantByAttributeValues($attributeValues)
            : $this->getDefaultVariant();
    }

    /**
     * Chech product must have variants or nit
     *
     * @return boolean
     */
    public function mustHaveVariants()
    {
        return 0 < $this->getVariantsAttributes()->count();
    }

    /**
     * Chech product has variants or nit
     *
     * @return boolean
     */
    public function hasVariants()
    {
        return 0 < $this->getVariants()->count();
    }

    /**
     * Flag if the product is not available according inventory configuration
     *
     * @return boolean
     */
    public function isOutOfStock()
    {
        return $this->getDefaultVariant()
            ? $this->getDefaultVariant()->isOutOfStock()
            : parent::isOutOfStock();
    }

    /**
     * Get all possible variants count
     *
     * @return integer
     */
    public function getAllPossibleVariantsCount()
    {
        $result = 1;

        foreach ($this->getMultipleAttributes() as $a) {
            if (in_array($a->getId(), $this->getVariantsAttributeIds())) {
                $result *= count($a->getAttributeValue($this));
            }
        }

        return $result;
    }

    /**
     * Get variants attribute ids
     *
     * @return array
     */
    public function getVariantsAttributeIds()
    {
        $variantsAttributeIds = array();

        foreach ($this->getVariantsAttributes() as $va) {
            $variantsAttributeIds[$va->getId()] = $va->getId();
        }

        return $variantsAttributeIds;
    }

    /**
     * Return true if product has incomplete variants list
     *
     * @return boolean
     */
    public function hasIncompleteVariantsList()
    {
        $allVariantsCount = $this->getAllPossibleVariantsCount();

        return 0 < $allVariantsCount && count($this->variants) < $allVariantsCount;
    }

    /**
     * Get clear weight
     *
     * @return float
     */
    public function getClearWeight()
    {
        return $this->getDefaultVariant()
            ? $this->getDefaultVariant()->getClearWeight()
            : parent::getClearWeight();
    }

    /**
     * Check variants
     *
     * @return void
     */
    public function checkVariants()
    {
        $changed = false;

        foreach ($this->getVariantsAttributes() as $va) {
            if (!$va->isMultiple($this)) {
                $this->getVariantsAttributes()->removeElement($va);
                $va->getVariantsProducts()->removeElement($this);
                $changed = true;
            }
        }

        if (0 < $this->getVariants()->count()) {
            if (0 == $this->getVariantsAttributes()->count()) {
                \XLite\Core\Database::getRepo('\XLite\Module\XC\ProductVariants\Model\ProductVariant')->deleteInBatch(
                    $this->getVariants()->toArray()
                );

                $this->getVariants()->clear();
                $changed = true;

            } else {
                foreach ($this->getVariantsAttributes() as $a) {
                    $variantsAttributes[$a->getId()] = $a->getId();
                }

                foreach ($this->getVariants() as $variant) {
                    $toAdd = $variantsAttributes;

                    foreach ($variant->getValues() as $v) {
                        $attribute = $v->getAttribute();
                        if (isset($toAdd[$attribute->getId()])) {
                            unset($toAdd[$attribute->getId()]);

                        } else {
                            $method = 'getAttributeValue' . $attribute->getType();
                            $variant->$method()->removeElement($v);
                            $v->getVariants()->removeElement($variant);
                            $changed = true;
                        }
                    }

                    if ($toAdd) {
                        $attributes = \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->findByIds($toAdd);
                        foreach ($attributes as $a) {
                            $aValue = $a->getAttributeValue($this);
                            $method = 'addAttributeValue' . $a->getType();
                            $attributeValue = array_shift($aValue);
                            $variant->$method($attributeValue);
                            $attributeValue->addVariants($variant);
                            $changed = true;
                        }
                    }
                }

                foreach ($this->getVariants() as $v) {
                    if (!isset($checked[$v->getId()])) {
                        if ($v->getValues()) {
                            $hash = $v->getValuesHash();
                            foreach ($this->getVariants() as $v2) {
                                if (
                                    $v->getId() != $v2->getId()
                                    && $v2->getValues()
                                    && !isset($checked[$v2->getId()])
                                ) {
                                    if ($v2->getValuesHash() === $hash) {
                                        $changed = true;
                                        \XLite\Core\Database::getEM()->remove($v2);
                                        $checked[$v2->getId()] = true;
                                    }
                                }
                            }

                        } else {
                            $changed = true;
                            \XLite\Core\Database::getEM()->remove($v);
                        }
                        $checked[$v->getId()] = true;
                    }
                }
            }
        }

        if ($changed) {
            $this->updateQuickData();
            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $newProduct = parent::cloneEntity();

        if ($this->mustHaveVariants()) {
            $attrs = array();
            foreach ($this->getVariantsAttributes() as $a) {
                if ($a->getProduct()) {
                    $cnd = new \XLite\Core\CommonCell();
                    $cnd->product = $newProduct;
                    $cnd->name = $a->getName();
                    $cnd->type = $a->getType();
                    $attribute = array_pop(\XLite\Core\Database::getRepo('\XLite\Model\Attribute')->search($cnd));

                } else {
                    $attribute = $a;
                }

                $attrs[$a->getId()] = $attribute;

                $newProduct->addVariantsAttributes($attribute);
                $attribute->addVariantsProduct($newProduct);
            }

            foreach ($this->getVariants() as $variant) {
                $newVariant = $variant->cloneEntity();
                $newVariant->setProduct($newProduct);
                $newProduct->addVariants($newVariant);
                \XLite\Core\Database::getEM()->persist($newVariant);

                foreach ($variant->getValues() as $av) {
                    $attribute = $attrs[$av->getAttribute()->getId()];
                    foreach ($attribute->getAttributeValue($newProduct) as $v) {
                        if ($v->asString() === $av->asString()) {
                            $method = 'addAttributeValue' . $attribute->getType();
                            $newVariant->$method($v);
                            $v->addVariants($newVariant);
                        }
                    }
                }
            }

            $newProduct->update(true);
        }

        return $newProduct;
    }

    /**
     * Preprocess change product class
     *
     * @return void
     */
    protected function preprocessChangeProductClass()
    {
        parent::preprocessChangeProductClass();

        $changed = false;

        foreach ($this->getVariantsAttributes() as $va) {
            if (
                $va->getProductClass()
                && $va->getProductClass() == $this->productClass->getId()
            ) {
                $this->getVariantsAttributes()->removeElement($va);
                $va->getVariantsProducts()->removeElement($this);
                $changed = true;
            }
        }

        if ($changed) {
            $this->checkVariants();
        }
    }
}
