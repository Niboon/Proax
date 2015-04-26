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
 * Product class
 *
 * @Entity
 * @Table  (name="product_classes")
 */
class ProductClass extends \XLite\Model\Base\I18n
{
    /**
     * ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position = 0;

    /**
     * Attributes
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\Attribute", mappedBy="productClass", cascade={"all"})
     */
    protected $attributes;

    /**
     * Attribute groups
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\AttributeGroup", mappedBy="productClass", cascade={"all"})
     * @OrderBy   ({"position" = "ASC"})
     */
    protected $attribute_groups;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->shipping_methods = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attribute_groups = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Return number of products associated with the category
     *
     * @return integer
     */
    public function getProductsCount()
    {
        return $this->getProducts(null, true);
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition OPTIONAL
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    public function getProducts(\XLite\Core\CommonCell $cnd = null, $countOnly = false)
    {
        if ($this->isPersistent()) {

            if (!isset($cnd)) {
                $cnd = new \XLite\Core\CommonCell();
            }

            // Main condition for this search
            $cnd->{\XLite\Model\Repo\Product::P_PRODUCT_CLASS} = $this;

            $result = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd, $countOnly);

        } else {
            $result = $countOnly ? 0 : array();
        }

        return $result;
    }

    /**
     * Return number of attributes associated with this class
     *
     * @return integer
     */
    public function getAttributesCount()
    {
        return count($this->getAttributes());
    }
}
