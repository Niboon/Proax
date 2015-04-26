<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomFields\Model;

class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Number of items sold in a pack
     *
     * @var     integer
     *
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $packSize;

    /**
     * Number of incoming stock quantities
     *
     * @var     integer
     *
     * @Column (type="integer", nullable=true, options={ "unsigned": true })
     */
    protected $POQty;


    /**
     * Number of items in stock for each location
     *
     * @Column (type="string", length=255)
     */
    protected $available;

    // Changed SKU to 40 chars, 2Feb2015 Niboon
    /**
     * Product SKU
     *
     * @var string
     *
     * @Column (type="string", length=40, nullable=true)
     */
    protected $sku;


    /**
     * Link for Technical Specifications Sheet
     *
     * @Column (type="string", length=255)
     */
    protected $techData;

    /**
     * Category Matching string that separates subcategories with a semicolon
     *
     * @var     integer
     *
     * @Column (type="string", length=255, nullable=true)
     */
    protected $categoryMatcher;
}