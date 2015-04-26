<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomCategoryFilter\View\ItemsList\Product\Customer;

use Doctrine\DBAL\Query\QueryException;
use mysqli;

/**
 * Custom Category Filtered Result
 *
 * @LC_Dependencies ("ProaxIT\SphinxSearch")
 */
class Search extends \XLite\View\ItemsList\Product\Customer\Search
{

    /**
     * Widget parameter names
     */
    const PARAM_CATEGORY_PATH = 'category_path';
    /**
     * Widget parameter names
     */
    const PARAM_ATTRIBUTE = 'attribute';

    /**
     * Items count before filter
     *
     * @var integer
     */
    protected $itemsCountBefore;

    /**
     * Widget target
     */
    const WIDGET_TARGET = 'search_filter';

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array(self::WIDGET_TARGET);
    }

    /**
     * Get session cell name for the certain list items widget
     *
     * @return string
     */
    public static function getSessionCellName()
    {
        return parent::getSessionCellName()
        . \XLite\Core\Request::getInstance()->{self::PARAM_CATEGORY_ID}
        . \XLite\Core\Request::getInstance()->{self::PARAM_CATEGORY_ID}
        . \XLite\Core\Request::getInstance()->{self::PARAM_ATTRIBUTE};
    }

    /**
     * Return target to retrive this widget from AJAX
     *
     * @return string
     */
    protected static function getWidgetTarget()
    {
        return self::WIDGET_TARGET;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/ProaxIT/CustomCategoryFilter/category_filter/style.css';

        return $list;
    }

    /**
     * Return search parameters.
     * :TODO: refactor
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return parent::getSearchParams()  + array(
            "categoryPath"   => self::PARAM_CATEGORY_PATH,
            "attribute"   => self::PARAM_ATTRIBUTE,
        );
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    public function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();
        if (!isset($cnd)) {
            $cnd = new \XLite\Core\CommonCell();
        }
        $cnd->{$this::PARAM_CATEGORY_PATH} = $this->getCategoryPath() ? : \XLite\Core\Request::getInstance()->categoryPath;
        $cnd->{$this::PARAM_CATEGORY_PATH} = $this->getAttribute() ? : \XLite\Core\Request::getInstance()->attribute;

        return $cnd;
    }

    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' filtered-products';
    }

    /**
     * Return number of items in products list before filter
     *
     * @return array
     */
    protected function getItemsCountBefore()
    {
        if (!isset($this->itemsCountBefore)) {
            $this->itemsCountBefore = parent::getData($this->getSearchCondition(), true);
        }

        return $this->itemsCountBefore;
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
            self::PARAM_CATEGORY_PATH => new \XLite\Model\WidgetParam\String(
                'Category Path', ''
            ),
            self::PARAM_ATTRIBUTE => new \XLite\Model\WidgetParam\String(
                'Attribute', ''
            ),
        );
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();


        $this->requestParams = array_merge(
            $this->requestParams,
            \XLite\View\ItemsList\Product\Customer\Search::getSearchParams()
        );
    }

    /**
     * Check if header is visible
     *
     * @return boolean
     */
    protected function isHeaderVisible()
    {
        return $this->hasResults();
    }

    /**
     * Check if pager is visible
     *
     * @return boolean
     */
    protected function isPagerVisible()
    {
        return $this->hasResults();
    }

    /**
     * Get empty list template
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return 'modules/ProaxIT/CustomCategoryFilter/category_tree/empty.tpl';
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isDisplayWithEmptyList()
    {
        return true;
    }

    /**
     * Return name of the session cell identifier
     *
     * @return string
     */
    protected function getSessionCell()
    {
        return parent::getSessionCell()
        . \XLite\Core\Request::getInstance()->{self::PARAM_CATEGORY_ID}
        . \XLite\Core\Request::getInstance()->{self::PARAM_CATEGORY_PATH}
        . \XLite\Core\Request::getInstance()->{self::PARAM_ATTRIBUTE};
    }

    /**
     * Prepare search condition before search
     *
     * @param \XLite\Core\CommonCell $cnd Search condition
     *
     * @return \XLite\Core\CommonCell
     */
    protected function prepareCnd(\XLite\Core\CommonCell $cnd)
    {
        $cnd = parent::prepareCnd($cnd);

        $cnd->categoryPath = $this->getWidgetParams(self::PARAM_CATEGORY_PATH)->value;
        $cnd->attribute = $this->getWidgetParams(self::PARAM_ATTRIBUTE)->value;

        return $cnd;
    }
}
