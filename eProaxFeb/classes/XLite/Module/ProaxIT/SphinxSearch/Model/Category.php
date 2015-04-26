<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\SphinxSearch\Model;

use XLite\Base\IDecorator;
use XLite\Core\Database;

/**
 * Category
 */
class Category extends \XLite\Model\Category implements IDecorator
{
    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition OPTIONAL
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    public function getProducts(\XLite\Core\CommonCell $cnd = null, $countOnly = false)
    {
        if (!isset($cnd)) {
            $cnd = new \XLite\Core\CommonCell();
        }

        // Main condition for this search
        $cnd->{\XLite\Model\Repo\Product::P_CATEGORY_ID} = $this->getCategoryId();
        $category = \XLite\Core\Database::getRepo('XLite\Model\Category')->find($cnd->{\XLite\Model\Repo\Product::P_CATEGORY_ID});

        if ($category) {
            $cnd->{"categoryPath"} = "ROOT;" . $category->getName() . ";";
        } else {
            $search = \XLite\View\ItemsList\Product\Customer\Search::getInstance();
            $cnd->{"categoryPath"} =  $search ? $search->get("categoryPath") : null;
        }

        if (!\XLite::isAdminZone()
            && 'directLink' != \XLite\Core\Config::getInstance()->General->show_out_of_stock_products
        ) {
            $cnd->{\XLite\Model\Repo\Product::P_INVENTORY} = false;
        }

        return \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd, $countOnly);
    }

}
