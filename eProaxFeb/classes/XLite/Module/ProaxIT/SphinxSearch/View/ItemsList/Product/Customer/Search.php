<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\SphinxSearch\View\ItemsList\Product\Customer;

use Doctrine\DBAL\Query\QueryException;
use mysqli;

/**
 * SphinxSearch
 *
 */
class Search extends \XLite\View\ItemsList\Product\Customer\Search implements \XLite\Base\IDecorator
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $this->sortByModes = array(
            static::SORT_BY_MODE_AMOUNT => 'Quantity in Stock',
            static::SORT_BY_MODE_NAME   => 'Name',
            static::SORT_BY_MODE_SKU    => 'SKU',
            static::SORT_BY_MODE_PRICE  => 'Price',
        );
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return static::SORT_BY_MODE_AMOUNT;
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
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_INCLUDING => new \XLite\Model\WidgetParam\Set(
                'Including',
                \XLite\Model\Repo\Product::INCLUDING_ALL,
                array(
                    \XLite\Model\Repo\Product::INCLUDING_ALL,
                    \XLite\Model\Repo\Product::INCLUDING_ANY,
                    \XLite\Model\Repo\Product::INCLUDING_PHRASE,
                )
            ),
            'searchWithin' => new \XLite\Model\WidgetParam\Checkbox(
                'Search Within', 0
            )
        );
    }


    /**
     * Return search parameters.
     * :TODO: refactor
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return parent::getSearchParams() +
        array(
            'searchWithin'      => 'searchWithin',
        );
    }


    /**
     * getDefaultParams
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        return parent::getDefaultParams() + array(
            \XLite\View\ItemsList\Product\Customer\Search::PARAM_INCLUDING => \XLite\Model\Repo\Product::INCLUDING_ANY,
        );
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

        $cnd->sortBy = $this->getWidgetParams(self::PARAM_SORT_BY)->value;
        $cnd->sortOrder = $this->getWidgetParams(self::PARAM_SORT_ORDER)->value;
        $cnd->limit = $this->getWidgetParams(\XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE)->value;
        $cnd->pageId = $this->getWidgetParams(\XLite\View\Pager\APager::PARAM_PAGE_ID)->value;
        $cnd->searchWithin = $this->getWidgetParams('searchWithin')->value;

        return $cnd;
    }
}
