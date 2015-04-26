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
 * Category
 *
 * @Entity
 * @Table (name="category_products",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="pair", columns={"category_id","product_id"})
 *      },
 *      indexes={
 *          @Index (name="orderby", columns={"orderby"})
 *      }
 * )
 */
class CategoryProducts extends \XLite\Model\AEntity
{
    /**
     * Primary key
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Product position in the category
     *
     * @var integer
     *
     * @Column (type="integer", length=11, nullable=false)
     */
    protected $orderby = 0;

    /**
     * Relation to a category entity
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ManyToOne  (targetEntity="XLite\Model\Category", inversedBy="categoryProducts")
     * @JoinColumn (name="category_id", referencedColumnName="category_id", onDelete="CASCADE")
     */
    protected $category;

    /**
     * Relation to a product entity
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="categoryProducts")
     * @JoinColumn (name="product_id", referencedColumnName="product_id", onDelete="CASCADE")
     */
    protected $product;
}
