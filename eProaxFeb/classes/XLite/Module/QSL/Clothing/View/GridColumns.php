<?php
/**
 * Created by PhpStorm.
 * User: proaxit
 * Date: 2015-04-02
 * Time: 11:48 AM
 */

namespace XLite\Module\QSL\Clothing\View;

abstract class GridColumns extends \XLite\View\ItemsList\Product\Customer\ACustomer implements \XLite\Base\IDecorator
{
    const GRID_COLUMNS_MAX = 3;

    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[static::PARAM_GRID_COLUMNS] = new \XLite\Model\WidgetParam\Set('Number of columns (for Grid mode only)', $this::GRID_COLUMNS_MAX, true, $this->getGridColumnsRange());

    }

}