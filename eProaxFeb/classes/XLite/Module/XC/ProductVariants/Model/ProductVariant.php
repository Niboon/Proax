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
 * Product variant
 *
 * @Entity
 * @Table  (name="product_variants")
 */
class ProductVariant extends \XLite\Model\AEntity
{
    /**
     * Unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Product
     *
     * @var \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product")
     * @JoinColumn (name="product_id", referencedColumnName="product_id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * Price
     *
     * @var float
     *
     * @Column (
     *      type="money",
     *      precision=14,
     *      scale=4,
     *      options={
     *          @\XLite\Core\Doctrine\Annotation\Behavior (list={"taxable"}),
     *          @\XLite\Core\Doctrine\Annotation\Purpose (name="net", source="clear"),
     *          @\XLite\Core\Doctrine\Annotation\Purpose (name="display", source="net")
     *      }
     *  )
     */
    protected $price = 0.0000;

    /**
     * Default price flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $defaultPrice = true;

    /**
     * Amount
     *
     * @var integer
     *
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $amount = 0;

    /**
     * Default amount flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $defaultAmount = true;

    /**
     * Weight
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */

    protected $weight = 0.0000;

    /**
     * Default weight flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $defaultWeight = true;

    /**
     * Product SKU
     *
     * @var string
     *
     * @Column (type="string", length=32, nullable=true)
     */
    protected $sku;

    /**
     * Default flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $defaultValue = false;

    /**
     * Image
     *
     * @var \XLite\Module\XC\ProductVariants\Model\Image\ProductVariant\Image
     *
     * @OneToOne  (targetEntity="XLite\Module\XC\ProductVariants\Model\Image\ProductVariant\Image", mappedBy="product_variant", cascade={"all"})
     */
    protected $image;

    /**
     * Attribute value (checkbox)
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany (targetEntity="XLite\Model\AttributeValue\AttributeValueCheckbox", inversedBy="variants")
     * @JoinTable (
     *      name="product_variant_attribute_value_checkbox",
     *      joinColumns={@JoinColumn (name="variant_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="attribute_value_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $attributeValueC;

    /**
     * Attribute value (select)
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany (targetEntity="XLite\Model\AttributeValue\AttributeValueSelect", inversedBy="variants")
     * @JoinTable (
     *      name="product_variant_attribute_value_select",
     *      joinColumns={@JoinColumn (name="variant_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="attribute_value_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $attributeValueS;

    /**
     * Product order items
     *
     * @var \Doctrine\ORM\PersistentCollection
     *
     * @OneToMany (targetEntity="XLite\Model\OrderItem", mappedBy="variant")
     */
    protected $orderItems;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->attributeValueC = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributeValueS = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get attribute value
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     *
     * @return mixed
     */
    public function getAttributeValue(\XLite\Model\Attribute $attribute)
    {
        $result = null;

        foreach ($this->getValues() as $v) {
            if ($v->getAttribute()->getId() == $attribute->getId()) {
                $result = $v;
                break;
            }
        }

        return $result;
    }

    /**
     * Get attribute values
     *
     * @return array
     */
    public function getValues()
    {
        return array_merge(
            $this->getAttributeValueS()->toArray(),
            $this->getAttributeValueC()->toArray()
        );
    }

    /**
     * Increase / decrease product inventory amount
     *
     * @param integer $delta Amount delta
     *
     * @return void
     */
    public function changeAmount($delta)
    {
        if (!$this->getDefaultAmount()) {
            $this->setAmount($this->getAmount() + $delta);
        }
    }

    /**
     * Get attribute values hash
     *
     * @return string
     */
    public function getValuesHash()
    {
        $hash = array();
        foreach ($this->getValues() as $av) {
            $hash[] = $av->getAttribute()->getId() . '_' . $av->getId();
        }
        sort($hash);

        return md5(implode('_', $hash));
    }

    /**
     * Get clear price
     *
     * @return float
     */
    public function getClearPrice()
    {
        return $this->getDefaultPrice()
            ? $this->getProduct()->getPrice()
            : $this->getPrice();
    }

    /**
     * Get clear weight
     *
     * @return float
     */
    public function getClearWeight()
    {
        return $this->getDefaultWeight()
            ? $this->getProduct()->getWeight()
            : $this->getWeight();
    }

    /**
     * Get display sku
     *
     * @return float
     */
    public function getDisplaySku()
    {
        return $this->getSku() ?: $this->getProduct()->getSku();
    }

    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku()
    {
        return isset($this->sku) ? strval($this->sku) : null;
    }

    /**
     * Alias: is product in stock or not
     *
     * @return boolean
     */
    public function isOutOfStock()
    {
        return $this->getDefaultAmount()
            ? $this->getProduct()->getInventory()->isOutOfStock()
            : 0 >= $this->getAvailableAmount();
    }

    /**
     * Return product amount available to add to cart
     *
     * @return integer
     */
    public function getAvailableAmount()
    {
        return $this->getDefaultAmount()
            ? $this->getProduct()->getInventory()->getAvailableAmount()
            : max(0, $this->getAmount() - $this->getLockedAmount());
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $newEntity = parent::cloneEntity();

        if ($this->getSku()) {
            $newEntity->setSku(
                \XLite\Core\Database::getRepo('XLite\Module\XC\ProductVariants\Model\ProductVariant')->assembleUniqueSKU($this->getSku())
            );
        }

        return $newEntity;
    }

    /**
     * Return taxable
     *
     * @return boolean
     */
    public function getTaxable()
    {
        return $this->getProduct()->getTaxable();
    }

    /**
     * Get list of cart items containing current product
     *
     * @return array
     */
    protected function getLockedItems()
    {
        return !$this->getDefaultAmount()
            ? \XLite\Model\Cart::getInstance()->getItemsByVariantId($this->getId())
            : $this->getProduct()->getInventory()->getLockedItems();
    }

    /**
     * Return "locked" amount: items already added to the cart
     *
     * @return integer
     */
    protected function getLockedAmount()
    {
        return \Includes\Utils\ArrayManager::sumObjectsArrayFieldValues($this->getLockedItems(), 'getAmount', true);
    }
}
