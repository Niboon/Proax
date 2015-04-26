<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator\View\Tabs;

/**
 * Shipping setings tabs widget
 */
class ShippingSettings extends \XLite\View\Tabs\ShippingSettings implements \XLite\Base\IDecorator
{
    /**
     * Widget initialization
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->tabs['purolator'] = array(
            'title'    => 'Purolator settings',
            'template' => 'modules/ProaxIT/Purolator/main.tpl'
        );
    }
}
