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

namespace XLite\Controller\Customer;

/**
 * Products search
 */
class Search extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Get search condition parameter by name TODO refactor with XLite\Controller\Admin\ProductList::getCondition()
     *
     * @param string $paramName Name of parameter
     *
     * @return mixed
     */
    public function getCondition($paramName)
    {
        $searchParams = $this->getConditions();

        return isset($searchParams[$paramName])
            ? $searchParams[$paramName]
            : null;
    }

    /**
     * Return 'checked' attribute for parameter.
     *
     * @param string $paramName Name of parameter
     * @param mixed  $value     Value to check with OPTIONAL
     *
     * @return string
     */
    public function getChecked($paramName, $value = 'Y')
    {
        return $value === $this->getCondition($paramName) ? 'checked' : '';
    }

    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Search');
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return static::t('Search results');
    }

    /**
     * doActionSearch TODO refactor with XLite\Controller\Admin\ProductList::doActionSearch()
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $sessionCell    = \XLite\View\ItemsList\Product\Customer\Search::getSessionCellName();
        $searchParams   = \XLite\View\ItemsList\Product\Customer\Search::getSearchParams();

        $productsSearch = array();

        $cBoxFields     = array(
            \XLite\View\ItemsList\Product\Customer\Search::PARAM_SEARCH_IN_SUBCATS
        );

        foreach ($searchParams as $modelParam => $requestParam) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $productsSearch[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        foreach ($cBoxFields as $requestParam) {
            $productsSearch[$requestParam] = isset(\XLite\Core\Request::getInstance()->$requestParam)
                ? 1
                : 0;
        }

        \XLite\Core\Session::getInstance()->{$sessionCell} = $productsSearch;

        $this->setReturnURL($this->buildURL('search', '', array('mode' => 'search')));
    }

    /**
     * Get search conditions TODO refactor with XLite\Controller\Admin\ProductList::getConditions()
     *
     * @return array
     */
    protected function getConditions()
    {
        $searchParams = \XLite\Core\Session::getInstance()
            ->{\XLite\View\ItemsList\Product\Customer\Search::getSessionCellName()};

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }
}
