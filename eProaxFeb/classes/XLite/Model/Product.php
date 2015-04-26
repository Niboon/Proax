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

namespace XLite\Model;

/**
 * The "product" model class
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Product")
 * @Table  (name="products",
 *      indexes={
 *          @Index (name="sku", columns={"sku"}),
 *          @Index (name="price", columns={"price"}),
 *          @Index (name="weight", columns={"weight"}),
 *          @Index (name="free_shipping", columns={"free_shipping"}),
 *          @Index (name="customerArea", columns={"enabled","arrivalDate"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class Product extends \XLite\Model\Base\Catalog implements \XLite\Model\Base\IOrderItem
{
    /**
     * Product unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $product_id;

    /**
     * Product price
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
     * Product SKU
     *
     * @var string
     *
     * @Column (type="string", length=32, nullable=true)
     */
    protected $sku;

    /**
     * Is product available or not
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = true;

    /**
     * Product weight
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $weight = 0.0000;

    /**
     * Is product shipped in separate box
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $useSeparateBox = false;

    /**
     * Product box width
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $boxWidth = 0.0000;

    /**
     * Product box length
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $boxLength = 0.0000;

    /**
     * Product box height
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $boxHeight = 0.0000;

    /**
     * How many product items can be placed in a box
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $itemsPerBox = 1;

    /**
     * Flag: false - product is shippable, false - product is not shippable
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $free_shipping = false;

    /**
     * If false then the product is free from any taxes
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $taxable = true;

    /**
     * Custom javascript code
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $javascript = '';

    /**
     * Arrival date (UNIX timestamp)
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $arrivalDate = 0;

    /**
     * Creation date (UNIX timestamp)
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $date = 0;

    /**
     * Update date (UNIX timestamp)
     *
     * @var integer
     *
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $updateDate = 0;

    /**
     * Is product need process or not
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $needProcess = true;

    /**
     * Relation to a CategoryProducts entities
     *
     * @var \Doctrine\ORM\PersistentCollection
     *
     * @OneToMany (targetEntity="XLite\Model\CategoryProducts", mappedBy="product", cascade={"all"})
     * @OrderBy   ({"orderby" = "ASC"})
     */
    protected $categoryProducts;

    /**
     * Product order items
     *
     * @var \XLite\Model\OrderItem
     *
     * @OneToMany (targetEntity="XLite\Model\OrderItem", mappedBy="object")
     */
    protected $order_items;

    /**
     * Product images
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\Image\Product\Image", mappedBy="product", cascade={"all"})
     * @OrderBy   ({"orderby" = "ASC"})
     */
    protected $images;

    /**
     * Qty in stock
     *
     * @var \XLite\Model\Inventory
     *
     * @OneToOne (targetEntity="XLite\Model\Inventory", mappedBy="product", fetch="LAZY", cascade={"all"})
     */
    protected $inventory;

    /**
     * Product class (relation)
     *
     * @var \XLite\Model\ProductClass
     *
     * @ManyToOne  (targetEntity="XLite\Model\ProductClass")
     * @JoinColumn (name="product_class_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $productClass;

    /**
     * Tax class (relation)
     *
     * @var \XLite\Model\TaxClass
     *
     * @ManyToOne  (targetEntity="XLite\Model\TaxClass")
     * @JoinColumn (name="tax_class_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $taxClass;

    /**
     * Attributes
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\Attribute", mappedBy="product", cascade={"all"})
     * @OrderBy   ({"position" = "ASC"})
     */
    protected $attributes;

    /**
     * Attribute value (checkbox)
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\AttributeValue\AttributeValueCheckbox", mappedBy="product", cascade={"all"})
     */
    protected $attributeValueC;

    /**
     * Attribute value (text)
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\AttributeValue\AttributeValueText", mappedBy="product", cascade={"all"})
     */
    protected $attributeValueT;

    /**
     * Attribute value (select)
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\AttributeValue\AttributeValueSelect", mappedBy="product", cascade={"all"})
     */
    protected $attributeValueS;

    /**
     * Show product attributes in a separate tab
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $attrSepTab = true;

    /**
     * How much product is sold (used in Top selling products statistics)
     *
     * @var integer
     */
    protected $sold = 0;

    /**
     * Quick data
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\QuickData", mappedBy="product", cascade={"all"})
     */
    protected $quickData;

    /**
     * Storage of current attribute values according to the clear price will be calculated
     *
     * @var array
     */
    protected $attrValues = array();

    /**
     * Memberships
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ManyToMany (targetEntity="XLite\Model\Membership", inversedBy="products")
     * @JoinTable (name="product_membership_links",
     *      joinColumns={@JoinColumn (name="product_id", referencedColumnName="product_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="membership_id", referencedColumnName="membership_id", onDelete="CASCADE")}
     * )
     */
    protected $memberships;

    /**
     * Clean URLs
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\CleanURL", mappedBy="product", cascade={"all"})
     * @OrderBy   ({"id" = "ASC"})
     */
    protected $cleanURLs;

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        /** @var \XLite\Model\Product $newProduct */
        $newProduct = parent::cloneEntity();

        $this->cloneEntityScalar($newProduct);

        if (!$newProduct->update(true) || !$newProduct->getProductId()) {
            \XLite::getInstance()->doGlobalDie('Can not clone product');
        }

        $this->cloneEntityModels($newProduct);
        $this->cloneEntityCategories($newProduct);
        $this->cloneEntityAttributes($newProduct);
        $this->cloneEntityImages($newProduct);
        $this->cloneEntityInventory($newProduct);

        $newProduct->update(true);

        return $newProduct;
    }

    /**
     * Clone entity (scalar fields)
     *
     * @param \XLite\Model\Product $newProduct New product
     *
     * @return void
     */
    protected function cloneEntityScalar(\XLite\Model\Product $newProduct)
    {
        $newProduct->setSku(\XLite\Core\Database::getRepo('XLite\Model\Product')->assembleUniqueSKU($this->getSku()));
        $newProduct->setName($this->getCloneName($this->getName()));
    }

    /**
     * Clone entity (model fields)
     *
     * @param \XLite\Model\Product $newProduct New product
     *
     * @return void
     */
    protected function cloneEntityModels(\XLite\Model\Product $newProduct)
    {

        $newProduct->setTaxClass($this->getTaxClass());
        $newProduct->setProductClass($this->getProductClass());
    }

    /**
     * Clone entity (categories)
     *
     * @param \XLite\Model\Product $newProduct New product
     *
     * @return void
     */
    protected function cloneEntityCategories(\XLite\Model\Product $newProduct)
    {
        foreach ($this->getCategories() as $category) {
            $link = new \XLite\Model\CategoryProducts;
            $link->setProduct($newProduct);
            $link->setCategory($category);
            $newProduct->addCategoryProducts($link);
            \XLite\Core\Database::getEM()->persist($link);
        }
    }

    /**
     * Clone entity (attributes)
     *
     * @param \XLite\Model\Product $newProduct New product
     *
     * @return void
     */
    protected function cloneEntityAttributes(\XLite\Model\Product $newProduct)
    {
        foreach (\XLite\Model\Attribute::getTypes() as $type => $name) {
            $methodGet = 'getAttributeValue' . $type;
            $methodAdd = 'addAttributeValue' . $type;
            foreach ($this->$methodGet() as $value) {
                if (!$value->getAttribute()->getProduct()) {
                    $newValue = $value->cloneEntity();
                    $newValue->setProduct($newProduct);
                    $newProduct->$methodAdd($newValue);
                    \XLite\Core\Database::getEM()->persist($newValue);
                }
            }
        }

        foreach ($this->getAttributes() as $attribute) {
            $class = $attribute->getAttributeValueClass($attribute->getType());
            $repo = \XLite\Core\Database::getRepo($class);
            $methodAdd = 'addAttributeValue' . $attribute->getType();
            $newAttribute = $attribute->cloneEntity();
            $newAttribute->setProduct($newProduct);
            $newProduct->addAttributes($newAttribute);
            \XLite\Core\Database::getEM()->persist($newAttribute);
            foreach ($repo->findBy(array('attribute' => $attribute, 'product' => $this)) as $value) {
                $newValue = $value->cloneEntity();
                $newValue->setProduct($newProduct);
                $newValue->setAttribute($newAttribute);
                $newProduct->$methodAdd($newValue);
                \XLite\Core\Database::getEM()->persist($newValue);
            }
        }
    }

    /**
     * Clone entity (images)
     *
     * @param \XLite\Model\Product $newProduct New product
     *
     * @return void
     */
    protected function cloneEntityImages(\XLite\Model\Product $newProduct)
    {
        foreach ($this->getImages() as $image) {
            $newImage = $image->cloneEntity();
            $newImage->setProduct($newProduct);
            $newProduct->addImages($newImage);
        }
    }

    /**
     * Clone entity (inventory)
     *
     * @param \XLite\Model\Product $newProduct New product
     *
     * @return void
     */
    protected function cloneEntityInventory(\XLite\Model\Product $newProduct)
    {
        $inventory = $this->getInventory()->cloneEntity();
        $newProduct->setInventory($inventory);
        $inventory->setProduct($newProduct);
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     */
    public function __construct(array $data = array())
    {
        $this->categoryProducts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images           = new \Doctrine\Common\Collections\ArrayCollection();
        $this->order_items      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->memberships      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributeValueC  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributeValueS  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributeValueT  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributes       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->quickData        = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get object unique id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getProductId();
    }

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get price: modules should never overwrite this method
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get clear price: this price can be overwritten by modules
     *
     * @return float
     */
    public function getClearPrice()
    {
        return $this->getPrice();
    }

    /**
     * Get quick data price
     *
     * @return float
     */
    public function getQuickDataPrice()
    {
        $price = $this->getClearPrice();

        foreach ($this->prepareAttributeValues() as $av) {
            if (is_object($av)) {
                $price += $av->getAbsoluteValue('price');
            }
        }

        return $price;
    }

    /**
     * Get clear weight
     *
     * @return float
     */
    public function getClearWeight()
    {
        return $this->getWeight();
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getSoftTranslation()->getName();
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
     * Get quantity of the product
     *
     * @return integer
     */
    public function getQty()
    {
        return $this->getInventory()->getPublicAmount();
    }

    /**
     * Get image
     *
     * @return \XLite\Model\Image\Product\Image
     */
    public function getImage()
    {
        return $this->getImages()->get(0);
    }

    /**
     * Get public images
     *
     * @return array
     */
    public function getPublicImages()
    {
        return $this->getImages()->toArray();
    }

    /**
     * Get free shipping flag
     *
     * @return boolean
     */
    public function getFreeShipping()
    {
        return $this->free_shipping;
    }

    /**
     * Get shippable flag
     *
     * @return boolean
     */
    public function getShippable()
    {
        return !$this->getFreeShipping();
    }

    /**
     * Set shippable flag
     *
     * @param boolean $value Value
     *
     * @return void
     */
    public function setShippable($value)
    {
        $this->setFreeShipping(!$value);
    }

    /**
     * Check if product is accessible
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return \XLite::isAdminZone() || $this->isPublicAvailable();
    }

    /**
     * Check product availability for public usage (customer interface)
     *
     * @return boolean
     */
    public function isPublicAvailable()
    {
        return $this->isVisible()
            && $this->availableInDate()
            && !$this->isOutOfStock();
    }

    /**
     * Check product visibility
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->getEnabled()
            && $this->hasAvailableMembership();
    }

    /**
     * Get membership Ids
     *
     * @return array
     */
    public function getMembershipIds()
    {
        $result = array();

        foreach ($this->getMemberships() as $membership) {
            $result[] = $membership->getMembershipId();
        }

        return $result;
    }

    /**
     * Flag if the category and active profile have the same memberships. (when category is displayed or hidden)
     *
     * @return boolean
     */
    public function hasAvailableMembership()
    {
        return 0 == $this->getMemberships()->count()
            || in_array(\XLite\Core\Auth::getInstance()->getMembershipId(), $this->getMembershipIds());
    }

    /**
     * Flag if the product is available according date/time
     *
     * @return boolean
     */
    public function availableInDate()
    {
        return !$this->getArrivalDate()
            || static::getUserTime() > $this->getArrivalDate();
    }

    /**
     * Check if product has image or not
     *
     * @return boolean
     */
    public function hasImage()
    {
        return !is_null($this->getImage()) && $this->getImage()->isPersistent();
    }

    /**
     * Return image URL
     *
     * @return string|void
     */
    public function getImageURL()
    {
        return $this->getImage() ? $this->getImage()->getURL() : null;
    }

    /**
     * Return random product category
     *
     * @param integer|null $categoryId Category ID OPTIONAL
     *
     * @return \XLite\Model\Category
     */
    public function getCategory($categoryId = null)
    {
        $result = $this->getLink($categoryId)->getCategory();

        if (empty($result)) {
            $result = new \XLite\Model\Category();
        }

        return $result;
    }

    /**
     * Return random product category ID
     *
     * @param integer|null $categoryId Category ID OPTIONAL
     *
     * @return integer
     */
    public function getCategoryId($categoryId = null)
    {
        return $this->getCategory($categoryId)->getCategoryId();
    }

    /**
     * Return list of product categories
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function getCategories()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Category')->findAllByProductId($this->getProductId());
    }

    /**
     * Setter
     *
     * @param double $value Value to set
     *
     * @return void
     */
    public function setAmount($value)
    {
        $this->getInventory()->setAmount($value);
    }

    /**
     * Get product Url
     *
     * @return string
     */
    public function getURL()
    {
        return $this->getProductId()
            ? \XLite\Core\Converter::makeURLValid(
                \XLite\Core\Converter::buildURL('product', '', array('product_id' => $this->getProductId()))
            )
            : null;
    }

    /**
     * Get front URL
     *
     * @return string
     */
    public function getFrontURL()
    {
        return $this->getProductId()
            ? \XLite\Core\Converter::makeURLValid(
                \XLite::getInstance()->getShopURL(
                    \XLite\Core\Converter::buildURL('product', '', array('product_id' => $this->getProductId()), \XLite::CART_SELF)
                )
            )
            : null;
    }

    /**
     * Minimal available amount
     *
     * @return integer
     */
    public function getMinPurchaseLimit()
    {
        return 1;
    }

    /**
     * Maximal available amount
     *
     * @return integer
     */
    public function getMaxPurchaseLimit()
    {
        return intval(\XLite\Core\Config::getInstance()->General->default_purchase_limit);
    }

    /**
     * Get inventory
     *
     * @return \XLite\Model\Inventory
     */
    public function getInventory()
    {
        if (isset($this->inventory)) {
            $inventory = $this->inventory;

        } else {
            $inventory = new \XLite\Model\Inventory();
            $inventory->setProduct($this);

        }

        return $inventory;
    }

    /**
     * Return product amount available to add to cart
     *
     * @return integer
     */
    public function getAvailableAmount()
    {
        return $this->getInventory()->getAvailableAmount();
    }

    /**
     * Alias: is product in stock or not
     *
     * @return boolean
     */
    public function isOutOfStock()
    {
        return $this->getInventory()->isOutOfStock();
    }

    /**
     * Return product position in category
     *
     * @param integer|null $categoryId Category ID OPTIONAL
     *
     * @return integer|void
     */
    public function getOrderBy($categoryId = null)
    {
        $link = $this->getLink($categoryId);

        return $link ? $link->getOrderBy() : null;
    }

    /**
     * Count product images
     *
     * @return integer
     */
    public function countImages()
    {
        return count($this->getPublicImages());
    }

    /**
     * Try to fetch product description
     *
     * @return string
     */
    public function getCommonDescription()
    {
        return $this->getBriefDescription() ?: $this->getDescription();
    }

    /**
     * Get taxable basis
     *
     * @return float
     */
    public function getTaxableBasis()
    {
        return $this->getNetPrice();
    }

    /**
     * Prepare creation date
     *
     * @return void
     *
     * @PrePersist
     */
    public function prepareBeforeCreate()
    {
        $time = \XLite\Core\Converter::time();

        if (!$this->getDate()) {
            $this->setDate($time);
        }

        if (!$this->getArrivalDate()) {
            $this->setArrivalDate(mktime(0, 0, 0, date('m', $time), date('j', $time), date('Y', $time)));
        }

        $this->prepareBeforeUpdate();
    }

    /**
     * Prepare update date
     *
     * @return void
     *
     * @PreUpdate
     */
    public function prepareBeforeUpdate()
    {
        $this->setUpdateDate(\XLite\Core\Converter::time());

        if (\XLite\Core\Converter::isEmptyString($this->getSKU())) {
            $this->setSKU(null);
        }
    }

    /**
     * Prepare remove
     *
     * @return void
     *
     * @PreRemove
     */
    public function prepareBeforeRemove()
    {
        // No default actions. May be used in modules
    }

    /**
     * Set product class
     *
     * @param \XLite\Model\ProductClass $productClass Product class OPTIONAL
     *
     * @return \XLite\Model\Product
     */
    public function setProductClass(\XLite\Model\ProductClass $productClass = null)
    {
        if ($this->productClass
            && (
                !$productClass
                || $productClass->getId() != $this->productClass->getId()
            )
        ) {
            $this->preprocessChangeProductClass();
        }

        $this->productClass = $productClass;

        return $this;
    }

    /**
     * Get attr values
     *
     * @return array
     */
    public function getAttrValues()
    {
        return $this->attrValues;
    }

    /**
     * Set attr values
     *
     * @param array $value Value
     *
     * @return void
     */
    public function setAttrValues($value)
    {
        $this->attrValues = $value;
    }

    /**
     * Sort editable attributes
     *
     * @param array $a Attribute A
     * @param array $b Attribute B
     *
     * @return boolean
     */
    protected function sortEditableAttributes($a, $b)
    {
        return $a['position'] >= $b['position'];
    }

    /**
     * Get editable attributes
     *
     * @return array
     */
    public function getEditableAttributes()
    {
        $result = array();

        foreach (\XLite\Model\Attribute::getTypes() as $type => $name) {
            $class = \XLite\Model\Attribute::getAttributeValueClass($type);
            if (is_subclass_of($class, 'XLite\Model\AttributeValue\Multiple')) {
                $result = array_merge(
                    $result,
                    \XLite\Core\Database::getRepo($class)->findMultipleAttributes($this)
                );

            } elseif ('\XLite\Model\AttributeValue\AttributeValueText' == $class) {
                $result = array_merge(
                    $result,
                    \XLite\Core\Database::getRepo($class)->findEditableAttributes($this)
                );
            }
        }

        usort($result, array($this, 'sortEditableAttributes'));

        if ($result) {
            foreach ($result as $k => $v) {
                $result[$k] = $v[0];
            }
        }

        return $result;
    }

    /**
     * Get editable attributes ids
     *
     * @return array
     */
    public function getEditableAttributesIds()
    {
        $result = array();

        foreach ($this->getEditableAttributes() as $a) {
            $result[] = $a->getId();
        }
        sort($result);

        return $result;
    }

    /**
     * Check - product has editable attrbiutes or not
     *
     * @return boolean
     */
    public function hasEditableAttributes()
    {
        return 0 < count($this->getEditableAttributes());
    }

    /**
     * Get multiple attributes
     *
     * @return array
     */
    public function getMultipleAttributes()
    {
        $result = array();

        foreach (\XLite\Model\Attribute::getTypes() as $type => $name) {
            $class = \XLite\Model\Attribute::getAttributeValueClass($type);
            if (is_subclass_of($class, 'XLite\Model\AttributeValue\Multiple')) {
                $result = array_merge(
                    $result,
                    \XLite\Core\Database::getRepo($class)->findMultipleAttributes($this)
                );
            }
        }

        if ($result) {
            foreach ($result as $k => $v) {
                $result[$k] = $v[0];
            }
        }

        return $result;
    }

    /**
     * Get multiple attributes ids
     *
     * @return array
     */
    public function getMultipleAttributesIds()
    {
        $result = array();

        foreach ($this->getMultipleAttributes() as $a) {
            $result[] = $a->getId();
        }
        sort($result);

        return $result;
    }

    /**
     * Check - product has multiple attrbiutes or not
     *
     * @return boolean
     */
    public function hasMultipleAttributes()
    {
        return 0 < count($this->getMultipleAttributes());
    }

    /**
     * Update quick data
     *
     * @return void
     */
    public function updateQuickData()
    {
        if ($this->isPersistent()) {
            \XLite\Core\QuickData::getInstance()->updateProductData($this);
        }
    }

    /**
     * Prepare attribute values
     *
     * @param array $ids Request-based selected attribute values OPTIONAL
     *
     * @return array
     */
    public function prepareAttributeValues($ids = array())
    {
        $attributeValues = array();
        foreach ($this->getEditableAttributes() as $a) {
            if ($a->getType() == \XLite\Model\Attribute::TYPE_TEXT) {
                $attributeValues[$a->getId()] = array(
                    'attributeValue' => $a->getAttributeValue($this),
                    'value'          => isset($ids[$a->getId()]) ? $ids[$a->getId()] : $a->getAttributeValue($this)->getValue(),
                );

            } else {
                $attributeValues[$a->getId()] = $a->getDefaultAttributeValue($this);
                if (isset($ids[$a->getId()])) {
                    foreach ($a->getAttributeValue($this) as $av) {
                        if ($av->getId() == $ids[$a->getId()]) {
                            $attributeValues[$a->getId()] = $av;
                            break;
                        }
                    }
                }
            }

        }

        return $attributeValues;
    }

    /**
     * Define the specific clone name for the product
     *
     * @param string $name Product name
     *
     * @return string
     */
    protected function getCloneName($name)
    {
        return $name . ' [ clone ]';
    }

    /**
     * Preprocess change product class
     *
     * @return void
     */
    protected function preprocessChangeProductClass()
    {
        if ($this->productClass) {
            foreach ($this->productClass->getAttributes() as $a) {
                $class = $a->getAttributeValueClass($a->getType());
                $repo = \XLite\Core\Database::getRepo($class);
                foreach ($repo->findBy(array('product' => $this, 'attribute' => $a)) as $v) {
                    $repo->delete($v);
                }
            }
        }
    }

    /**
     * Return certain Product <--> Category association
     *
     * @param integer|null $categoryId Category ID
     *
     * @return \XLite\Model\CategoryProducts|void
     */
    protected function findLinkByCategoryId($categoryId)
    {
        $result = null;

        foreach ($this->getCategoryProducts() as $cp) {
            if ($cp->getCategory() && $cp->getCategory()->getCategoryId() == $categoryId) {
                $result = $cp;
            }
        }

        return $result;
    }

    /**
     * Return certain Product <--> Category association
     *
     * @param integer|null $categoryId Category ID OPTIONAL
     *
     * @return \XLite\Model\CategoryProducts
     */
    protected function getLink($categoryId = null)
    {
        $result = empty($categoryId)
            ? $this->getCategoryProducts()->first()
            : $this->findLinkByCategoryId($categoryId);

        if (empty($result)) {
            $result = new \XLite\Model\CategoryProducts();
        }

        return $result;
    }

    /**
     * Returns position of the product in the given category
     *
     * @param integer $category
     *
     * @return integer
     */
    public function getPosition($category)
    {
        return $this->getOrderBy($category);
    }

    /**
     * Sets the position of the product in the given category
     *
     * @param array $value
     *
     * @return void
     */
    public function setPosition($value)
    {
        $link = $this->getLink($value['category']);
        $link->setProduct($this);
        $link->setOrderby($value['position']);

        \XLite\Core\Database::getEM()->flush($link);
    }
}
