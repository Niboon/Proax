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

namespace XLite\Module\CDev\Sale\View;

/**
 * Sale products block widget
 *
 * @ListChild (list="center.bottom", zone="customer", weight="600")
 * @ListChild (list="sidebar.single", zone="customer", weight="170")
 * @ListChild (list="sidebar.first", zone="customer", weight="130")
 */
class SaleBlock extends \XLite\Module\CDev\Sale\View\ASale
{
    /**
     * Widget parameter
     */
    const PARAM_MAX_ITEMS_TO_DISPLAY = 'maxItemsToDisplay';

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();

        $result[] = 'main';
        $result[] = 'category';

        return $result;
    }

    /**
     * Initialize widget (set attributes)
     *
     * @param array $params Widget params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        unset($this->widgetParams[\XLite\View\Pager\APager::PARAM_SHOW_ITEMS_PER_PAGE_SELECTOR]);
        unset($this->widgetParams[\XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE]);
        unset($this->widgetParams[self::PARAM_SHOW_DISPLAY_MODE_SELECTOR]);
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_MAX_ITEMS_TO_DISPLAY => new \XLite\Model\WidgetParam\Int(
                'Maximum products to display', $this->getMaxCountInBlock(), true, true
            ),
        );

        $widgetType = \XLite\Core\Config::getInstance()->CDev->Sale->sale_menu
            ? self::WIDGET_TYPE_SIDEBAR
            : self::WIDGET_TYPE_CENTER;

        $this->widgetParams[self::PARAM_WIDGET_TYPE]->setValue($widgetType);
    }

    /**
     * Returns search products conditions
     *
     * @param \XLite\Core\CommonCell $cnd Initial search conditions
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchConditions(\XLite\Core\CommonCell $cnd)
    {
        $cnd = parent::getSearchConditions($cnd);

        $cnd->{\XLite\Model\Repo\Product::P_SEARCH_IN_SUBCATS} = true;

        if ($this->getCategoryId()) {
            $cnd->{\XLite\Model\Repo\Product::P_CATEGORY_ID} = $this->getCategoryId();
        }

        if ($this->getMaxItemsCount()) {
            $cnd->{\XLite\Model\Repo\Product::P_LIMIT} = array(
                0,
                $this->getMaxItemsCount()
            );
        }

        return $cnd;
    }

    /**
     * Returns maximum allowed items count
     *
     * @return integer
     */
    protected function getMaxItemsCount()
    {
        return $this->getParam(self::PARAM_MAX_ITEMS_TO_DISPLAY) ?: $this->getMaxCountInBlock();
    }

    /**
     * Return template of New arrivals widget. It depends on widget type:
     * SIDEBAR/CENTER and so on.
     *
     * @return string
     */
    protected function getTemplate()
    {
        $template = parent::getTemplate();

        if (
            $template == $this->getDefaultTemplate()
            && self::WIDGET_TYPE_SIDEBAR == $this->getParam(self::PARAM_WIDGET_TYPE)
        ) {
            $template = self::TEMPLATE_SIDEBAR;
        }

        return $template;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible()
            && \XLite\Core\Config::getInstance()->CDev->Sale->sale_enabled
            && static::getWidgetTarget() != \XLite\Core\Request::getInstance()->target
            && 0 < $this->getData(new \XLite\Core\CommonCell(), true);

        if ($result) {

            if (!\XLite\Core\CMSConnector::isCMSStarted()) {

                if (self::WIDGET_TYPE_SIDEBAR == $this->getParam(self::PARAM_WIDGET_TYPE)) {
                    $result = in_array($this->viewListName, array('sidebar.first', 'sidebar.single'));

                } elseif (self::WIDGET_TYPE_CENTER == $this->getParam(self::PARAM_WIDGET_TYPE)) {
                    $result = ('center.bottom' == $this->viewListName);
                }
            }
        }

        return $result;
    }

    /**
     * Get 'More...' link URL for Sale products list
     *
     * @return string
     */
    protected function getMoreLinkURL()
    {
        return $this->buildURL(self::WIDGET_TARGET_SALE_PRODUCTS);
    }

    /**
     * Get 'More...' link text for Sale products list
     *
     * @return string
     */
    protected function getMoreLinkText()
    {
        return static::t('All products on sale');
    }

    /**
     * Check status of 'More...' link for sidebar list
     *
     * @return boolean
     */
    protected function isShowMoreLink()
    {
        return true;
    }
}
