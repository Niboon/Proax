<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomFields\View\Model;

class Product extends \XLite\View\Model\Product implements \XLite\Base\IDecorator
{
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $this->schemaDefault += array (
            'packSize' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'Pack Size',
                self::SCHEMA_REQUIRED => false,
            ),
        );

        $this->schemaDefault += array (
            'POQty' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'P.O. Quantity',
                self::SCHEMA_REQUIRED => false,
            ),
        );

        $this->schemaDefault += array (
            'available' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'Stock Availability',
                self::SCHEMA_REQUIRED => false,
            ),
        );

        $this->schemaDefault += array (
            'techData' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'Technical Datasheet Link',
                self::SCHEMA_REQUIRED => false,
            ),
        );

        $this->schemaDefault += array (
            'categoryMatcher' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'Category Path Matcher',
                self::SCHEMA_REQUIRED => false,
            ),
        );
    }
}