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

namespace XLite\Module\CDev\ProductAdvisor\View;

/**
 * New arrivals abstract widget class
 *
 */
abstract class ANewArrivals extends \XLite\View\ItemsList\Product\Customer\ACustomer
{
    /**
     * Widget parameter names
     */

    const PARAM_ROOT_ID     = 'rootId';
    const PARAM_USE_NODE    = 'useNode';
    const PARAM_CATEGORY_ID = 'category_id';

    /**
     * Widget target
     */
    const WIDGET_TARGET_NEW_ARRIVALS = 'new_arrivals';


    /**
     * Return target to retrive this widget from AJAX
     *
     * @return string
     */
    protected static function getWidgetTarget()
    {
        return self::WIDGET_TARGET_NEW_ARRIVALS;
    }


    /**
     * We remove the sort by modes selector from the new arrivals widgets
     *
     * @param array $params Widget params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $this->sortByModes = array();
    }

    /**
     * getSortOrderDefault
     *
     * @return string
     */
    protected function getSortOrderModeDefault()
    {
        return static::SORT_ORDER_DESC;
    }

    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' new-arrivals-products';
    }


    /**
     * Get title
     *
     * @return string
     */
    protected function getHead()
    {
        return \XLite\Core\Translation::getInstance()->translate('New arrivals');
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\Module\CDev\ProductAdvisor\View\Pager\Pager';
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchConditions(\XLite\Core\CommonCell $cnd)
    {
        $currentDate = static::getUserTime();
        $daysOffset
            = abs(intval(\XLite\Core\Config::getInstance()->CDev->ProductAdvisor->na_max_days))
            ?: \XLite\Module\CDev\ProductAdvisor\Main::PA_MODULE_OPTION_DEFAULT_DAYS_OFFSET;

        $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_ARRIVAL_DATE} = array(
            $currentDate - 86400 * $daysOffset,
            $currentDate
        );

        $cnd->{\XLite\Model\Repo\Product::P_ORDER_BY} = array('p.arrivalDate', 'DESC');

        return $cnd;
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return mixed
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product')
            ->search($this->getSearchConditions($cnd), $countOnly);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->na_enabled;
    }

    /**
     * Set default sort mode by descending of products arrival date
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return self::SORT_BY_MODE_DATE;
    }

    /**
     * Get max number of products displayed in block
     *
     * @return integer
     */
    protected function getMaxCountInBlock()
    {
        return
            min(
                intval(\XLite\Core\Config::getInstance()->CDev->ProductAdvisor->na_max_count_in_block),
                $this->getMaxCountInFullList()
            )
            ?: 3;
    }

    /**
     * Get max number of products displayed in full list of new arrivals
     *
     * @return integer
     */
    protected function getMaxCountInFullList()
    {
        return intval(\XLite\Core\Config::getInstance()->CDev->ProductAdvisor->na_max_count_in_full_list);
    }

    // {{{ Cache

    /**
     * Cache allowed
     *
     * @param string $template Template
     *
     * @return boolean
     */
    protected function isCacheAllowed($template)
    {
        return parent::isCacheAllowed($template)
            && $this->isStaticProductList();
    }

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return true;
    }

    /**
     * Get cache TTL (seconds)
     *
     * @return integer
     */
    protected function getCacheTTL()
    {
        return 3600;
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' block-new-arrivals';
    }

    // }}}
}
