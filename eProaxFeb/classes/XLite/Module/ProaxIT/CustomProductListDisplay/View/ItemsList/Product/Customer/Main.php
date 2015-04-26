<?php
// vim: set ts=4 sw=4 sts=4 et:


namespace XLite\Module\ProaxIT\CustomProductListDisplay\View\ItemsList\Product\Customer;

/**
 * Replace default category browsing's product display view
 */
class Main extends \XLite\View\ItemsList\Product\Customer\Category\Main implements \XLite\Base\IDecorator
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = '/modules/ProaxIT/CustomProductListDisplay/css/style.css';

        return $list;
    }

    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[static::PARAM_DISPLAY_MODE] = new \XLite\Model\WidgetParam\Set('Display mode', static::DISPLAY_MODE_LIST, true, array());
    }

    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        // setting thumbnail sizes
        $this->widgetParams[static::PARAM_ICON_MAX_WIDTH]->setValue((int)"");
        $this->widgetParams[static::PARAM_ICON_MAX_HEIGHT]->setValue((int)"");
        if (!($this->getWidgetParams(self::PARAM_DISPLAY_MODE))) {
            // Setting the default items display view to a list
            $this->widgetParams[self::PARAM_DISPLAY_MODE]->setValue(self::DISPLAY_MODE_LIST);
        }
    }
}