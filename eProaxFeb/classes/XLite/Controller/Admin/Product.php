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

namespace XLite\Controller\Admin;

/**
 * Product
 */
class Product extends \XLite\Controller\Admin\ACL\Catalog
{
    /**
     * Backward compatibility
     *
     * @var array
     */
    protected $params = array('target', 'id', 'product_id', 'page', 'backURL');

    /**
     * Chuck length
     */
    const CHUNK_LENGTH = 100;

    // {{{ Abstract method implementations

    /**
     * Check if we need to create new product or modify an existing one
     *
     * NOTE: this function is public since it's neede for widgets
     *
     * @return boolean
     */
    public function isNew()
    {
        return !$this->getProduct()->isPersistent();
    }

    /**
     * Defines the product preview URL
     *
     * @param integer $productId Product id
     *
     * @return string
     */
    public function buildProductPreviewURL($productId)
    {
        return \XLite\Core\Converter::buildURL(
            'product',
            'preview',
            array('product_id' => $productId),
            \XLite::CART_SELF
        );
    }

    /**
     * Return model form object
     *
     * @param array $params Form constructor params OPTIONAL
     *
     * @return \XLite\View\Model\AModel|void
     */
    public function getInventoryModelForm(array $params = array())
    {
        $class = '\XLite\View\Model\InventoryTracking';

        return \XLite\Model\CachingFactory::getObject(
            __METHOD__ . $class . (empty($params) ? '' : md5(serialize($params))),
            $class,
            $params
        );
    }

    /**
     * Return class name for the controller main form
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return '\XLite\View\Model\Product';
    }

    /**
     * Alias
     *
     * @return \XLite\Model\Product
     */
    protected function getEntity()
    {
        return $this->getProduct();
    }

    // }}}

    // {{{ Pages

    /**
     * Get pages sections
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();
        $list['info'] = static::t('Info');

        if (!$this->isNew()) {
            $list['attributes'] = static::t('Attributes');
            $list['inventory']  = static::t('Inventory tracking');
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
        $list['info']    = 'product/info.tpl';
        $list['default'] = 'product/info.tpl';

        if (!$this->isNew()) {
            $list['attributes'] = 'product/attributes.tpl';
            $list['inventory']  = 'product/inventory.tpl';
        }

        return $list;
    }

    // }}}

    // {{{ Data management

    /**
     * Alias
     *
     * @return \XLite\Model\Product
     */
    public function getProduct()
    {
        $result = $this->productCache
            ?: \XLite\Core\Database::getRepo('\XLite\Model\Product')->find($this->getProductId());

        if (!isset($result)) {
            $result = new \XLite\Model\Product();
        }

        return $result;
    }

    /**
     * Returns the categories of the product
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->isNew()
            ? array(
                $this->getCategoryId(),
            )
            : $this->getProduct()->getCategories();
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getProduct()->getName();
    }

    /**
     * getInventory
     *
     * @return \XLite\Model\Inventory
     */
    public function getInventory()
    {
        return $this->getProduct()->getInventory();
    }

    /**
     * Get product category id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        $categoryId = parent::getCategoryId();

        if (empty($categoryId) && !$this->isNew()) {
            $categoryId = $this->getProduct()->getCategoryId();
        }

        return $categoryId;
    }

    /**
     * Return current product Id
     *
     * NOTE: this function is public since it's neede for widgets
     *
     * @return integer
     */
    public function getProductId()
    {
        $result = $this->productCache
            ? $this->productCache->getProductId()
            : intval(\XLite\Core\Request::getInstance()->product_id);

        if (0 >= $result) {
            $result = intval(\XLite\Core\Request::getInstance()->id);
        }

        return $result;
    }

    /**
     * The product can be set from the view classes
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return void
     */
    public function setProduct(\XLite\Model\Product $product)
    {
        $this->productCache = $product;
    }

    /**
     * Get posted data
     *
     * @param string $field Name of the field to retrieve OPTIONAL
     *
     * @return mixed
     */
    protected function getPostedData($field = null)
    {
        $value = parent::getPostedData($field);

        $time = \XLite\Core\Converter::time();

        if (!isset($field)) {
            if (isset($value['arrivalDate'])) {
                $value['arrivalDate'] = intval(strtotime($value['arrivalDate']))
                    ?: mktime(0, 0, 0, date('m', $time), date('j', $time), date('Y', $time));
            }

            if (isset($value['sku']) && \XLite\Core\Converter::isEmptyString($value['sku'])) {
                $value['sku'] = null;
            }

            if (isset($value['productClass'])) {
                $value['productClass'] = \XLite\Core\Database::getRepo('\XLite\Model\ProductClass')
                    ->find($value['productClass']);
            }

            if (isset($value['taxClass'])) {
                $value['taxClass'] = \XLite\Core\Database::getRepo('\XLite\Model\TaxClass')->find($value['taxClass']);
            }

        } elseif ('arrivalDate' === $field) {
            $value = intval(strtotime($value)) ?: mktime(0, 0, 0, date('m', $time), date('j', $time), date('Y', $time));

        } elseif ('sku' === $field) {
            $value = null;

        } elseif ('productClass' === $field) {
            $value = \XLite\Core\Database::getRepo('\XLite\Model\ProductClass')->find($value);

        } elseif ('taxClass' === $field) {
            $value = \XLite\Core\Database::getRepo('\XLite\Model\TaxClass')->find($value);
        }

        return $value;
    }

    // }}}

    // {{{ Action handlers

    /**
     * doActionUpdate
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $this->getModelForm()->performAction('modify');

        $this->setReturnURL(
            $this->buildURL(
                'product',
                '',
                array(
                    'product_id' => $this->getProductId()
                )
            )
        );
    }

    // TODO: refactor

    /**
     * Do action clone
     *
     * @return void
     */
    protected function doActionClone()
    {
        if ($this->getProduct()) {
            $newProduct = $this->getProduct()->cloneEntity();
            $newProduct->updateQuickData();
            $this->setReturnURL($this->buildURL('product', '', array('product_id' => $newProduct->getId())));
        }
    }

    /**
     * Update inventory
     *
     * @return void
     */
    protected function doActionUpdateInventory()
    {
        $this->getInventoryModelForm()->performAction('modify');
        /*
        $inv = $this->getInventory();

        $inv->map($this->getPostedData());

        if (!$inv->getInventoryId()) {
            $product = $this->getProduct();
            $product->setInventory($inv);
            $inv->setProduct($product);
            \XLite\Core\Database::getEM()->persist($inv);
        }

        \XLite\Core\Database::getEM()->flush();
        */
    }

    /**
     * Update attributes
     *
     * @return void
     */
    protected function doActionUpdateAttributes()
    {
        $name           = \XLite\Core\Request::getInstance()->name;
        $attributeValue = \XLite\Core\Request::getInstance()->attributeValue;
        $delete         = \XLite\Core\Request::getInstance()->delete;
        $newValue       = \XLite\Core\Request::getInstance()->newValue;
        $saveMode      = \XLite\Core\Request::getInstance()->save_mode;

        $repo      = \XLite\Core\Database::getRepo('\XLite\Model\Attribute');
        $repoGroup = \XLite\Core\Database::getRepo('\XLite\Model\AttributeGroup');

        if ('globaly' == $saveMode) {
            $oldValues = $this->getAttributeValues();
        }

        if ($delete) {
            foreach ($delete as $k => $v) {
                if (isset($name[$k])) {
                    unset($name[$k]);
                }
                if (isset($attributeValue[$k])) {
                    unset($attributeValue[$k]);
                }
                $a = $repo->find($k);
                if ($a) {
                    $repo->delete($repo->find($k), false);
                }
            }
        }

        if ($name) {
            $attributes = $repo->findByIds(array_keys($name));

            if ($attributes) {
                foreach ($attributes as $a) {
                    if ($name[$a->getId()]) {
                        $a->setName($name[$a->getId()]);
                    }
                }
            }
        }

        if ($attributeValue) {
            $attributes = $repo->findByIds(array_keys($attributeValue));

            if ($attributes) {
                foreach ($attributes as $a) {
                    if (isset($attributeValue[$a->getId()])) {
                        $a->setAttributeValue(
                            $this->getProduct(),
                            $attributeValue[$a->getId()]
                        );
                    }
                }
            }
        }

        if ($newValue) {
            foreach ($newValue as $data) {
                $data['name'] = trim($data['name']);
                if (
                    $data['name']
                    && $data['type']
                    && \XLite\Model\Attribute::getTypes($data['type'])
                ) {
                    $a = new \XLite\Model\Attribute();
                    $a->setName($data['name']);
                    $a->setType($data['type']);
                    if (0 < $data['listId']) {
                        $group = $repoGroup->find($data['listId']);
                        if ($group) {
                            $a->setAttributeGroup($group);
                            $a->setProductClass($group->getProductClass());
                        }

                    } elseif (
                        -2 == $data['listId']
                        && $this->getProduct()->getProductClass()
                    ) {
                        $a->setProductClass($this->getProduct()->getProductClass());

                    } elseif (-3 == $data['listId']) {
                        $a->setProduct($this->getProduct());
                        $this->getProduct()->addAttributes($a);
                    }

                    unset($data['name'], $data['type']);
                    $repo->insert($a);

                    $a->setAttributeValue($this->getProduct(), $data);
                }
            }
        }

        $this->getProduct()->updateQuickData();

        if ('globaly' == $saveMode) {
            $this->applyAttributeValuesChanges(
                $oldValues,
                $this->getAttributeValues()
            );
        }

        \XLite\Core\Database::getEM()->flush();
        \XLite\Core\TopMessage::addInfo('Attributes have been updated successfully');
    }

    /**
     * Update attributes properties
     *
     * @return void
     */
    protected function doActionUpdateAttributesProperties()
    {
        $list = new \XLite\View\ItemsList\AttributeProperty;
        $list->processQuick();
    }

    /**
     * Get attribute values for diff
     *
     * @return array
     */
    protected function getAttributeValues()
    {
        $result = array();

        foreach (\XLite\Model\Attribute::getTypes() as $type => $name) {
            $class = \XLite\Model\Attribute::getAttributeValueClass($type);
            $result[$type] = \XLite\Core\Database::getRepo($class)->findCommonValues($this->getProduct());
        }

        return $result;
    }

    /**
     * Apply attribute values changes
     *
     * @param array $oldValues Old values
     * @param array $newValues New values
     *
     * @return void
     */
    protected function applyAttributeValuesChanges(array $oldValues, array $newValues)
    {
        $diff = array();
        foreach (\XLite\Model\Attribute::getTypes() as $type => $name) {
            $class = \XLite\Model\Attribute::getAttributeValueClass($type);
            $diff += $class::getDiff($oldValues[$type], $newValues[$type]);
        }

        if ($diff) {
            $i = 0;
            do {
                $processed = 0;
                foreach (\XLite\Core\Database::getRepo('XLite\Model\Product')->findFrame($i, static::CHUNK_LENGTH) as $product) {
                    foreach ($diff as $attributeId => $changes) {
                        $attribute = \XLite\Core\Database::getRepo('XLite\Model\Attribute')->find($attributeId);
                        $attribute->applyChanges($product, $changes);
                    }

                    $processed++;
                }

                if (0 < $processed) {
                    \XLite\Core\Database::getEM()->flush();
                    \XLite\Core\Database::getEM()->clear();
                }
                $i += $processed;

            } while (0 < $processed);
        }
    }

    /**
     * Update product class
     *
     * @return void
     */
    protected function doActionUpdateProductClass()
    {
        $updateClass = false;

        if (-1 == \XLite\Core\Request::getInstance()->productClass) {
            $name = trim(\XLite\Core\Request::getInstance()->newProductClass);

            if ($name) {
                $productClass = new \XLite\Model\ProductClass;
                $productClass->setName($name);
                \XLite\Core\Database::getRepo('\XLite\Model\ProductClass')->insert($productClass);
                $updateClass = true;
            }

        } else {
            $productClass = \XLite\Core\Database::getRepo('\XLite\Model\ProductClass')->find(
                \XLite\Core\Request::getInstance()->productClass
            );
            $updateClass = true;
        }

        if ($updateClass) {
            $productClassChanged = $productClass
                && (
                    !$this->getProduct()->getProductClass()
                    || $productClass->getId() != $this->getProduct()->getProductClass()->getId()
                );

            $this->getProduct()->setProductClass($productClass);

            if ($productClassChanged) {
                \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->generateAttributeValues(
                    $this->getProduct(),
                    true
                );
            }

            \XLite\Core\Database::getEM()->flush();
            \XLite\Core\TopMessage::addInfo('Product class have been updated successfully');

        } else {
            \XLite\Core\TopMessage::addWarning('Product class name is empty');
        }
    }

    // }}}
}
