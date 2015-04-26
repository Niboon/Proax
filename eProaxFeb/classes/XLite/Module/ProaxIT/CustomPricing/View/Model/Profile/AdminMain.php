<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomPricing\View\Model\Profile;

class AdminMain extends \XLite\View\Model\Profile\AdminMain implements \XLite\Base\IDecorator
{
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $this->summarySchema += array (
            'customerId' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text',
                self::SCHEMA_LABEL    => 'Customer Id',
                self::SCHEMA_REQUIRED => false,
            ),
        );

        $this->summarySchema += array (
            'preferredShipping' => array(
                self::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Checkbox\Enabled',
                self::SCHEMA_LABEL    => 'Preferred Shipping Method'
            ),
        );
    }
}