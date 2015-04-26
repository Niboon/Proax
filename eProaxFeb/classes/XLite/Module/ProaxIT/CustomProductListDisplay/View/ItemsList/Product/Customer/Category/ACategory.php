<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomProductListDisplay\View\ItemsList\Product\Customer\Category;

/**
 * Replace default category browsing's product display view
 */
abstract class ACategory extends \XLite\View\ItemsList\Product\Customer\Category\ACategory implements \XLite\Base\IDecorator
{
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[static::PARAM_DISPLAY_MODE] = new \XLite\Model\WidgetParam\Set('Display mode', static::DISPLAY_MODE_LIST, true, array());
    }

    public function setWidgetParams (array $params)
    {
        parent::setWidgetParams($params);
        if (!($this->getWidgetParams(self::PARAM_DISPLAY_MODE))) {
            $this->widgetParams[static::PARAM_DISPLAY_MODE]->setValue(static::DISPLAY_MODE_LIST);
        }
    }
}