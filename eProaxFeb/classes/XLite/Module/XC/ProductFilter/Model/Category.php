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

namespace XLite\Module\XC\ProductFilter\Model;

/**
 * Category
 *
 */
class Category extends \XLite\Model\Category implements \XLite\Base\IDecorator
{
    /**
     * 'Use classes' values
     */
    const USE_CLASSES_NO     = 'N';
    const USE_CLASSES_AUTO   = 'A';
    const USE_CLASSES_DEFINE = 'D';

    /**
     * Category classes
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ManyToMany (targetEntity="XLite\Model\ProductClass", inversedBy="categories")
     * @JoinTable (name="category_class_links",
     *      joinColumns={@JoinColumn (name="category_id", referencedColumnName="category_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="class_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @OrderBy   ({"position" = "ASC"})
     */
    protected $productClasses;

    /**
     * Status code
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $useClasses = self::USE_CLASSES_AUTO;

    /**
     * Return list of all allowed 'Use classes' value
     *
     * @param string $status Value to get OPTIONAL
     *
     * @return array | string
     */
    public static function getAllowedUseClasses($status = null)
    {
        $list = array(
            self::USE_CLASSES_AUTO    => 'This category classes',
            self::USE_CLASSES_NO      => 'Do not show the filter',
            self::USE_CLASSES_DEFINE  => 'Defined by admin',
        );

        return isset($status)
            ? (isset($list[$status]) ? $list[$status] : null)
            : $list;
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->productClasses = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Set status
     *
     * @param string $value Status code
     *
     * @return void
     */
    public function setStatus($value)
    {
        $this->oldStatus = $this->status != $value ? $this->status : null;
        $allowedUseClasses = static::getAllowedUseClasses();
        if (isset($allowedUseClasses[$value])) {
            $this->status = $value;
        }
    }
}
