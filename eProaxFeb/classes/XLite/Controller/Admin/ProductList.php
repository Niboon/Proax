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

namespace XLite\Controller\Admin;

/**
 * Products list controller
 */
class ProductList extends \XLite\Controller\Admin\ACL\Catalog
{
    /**
     * Get search condition parameter by name
     *
     * @param string $paramName Parameter name
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
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Products');
    }

    /**
     * Check - search panel is visible or not
     *
     * @return boolean
     */
    public function isSearchVisible()
    {
        return 0 < \XLite\Core\Database::getRepo('XLite\Model\Product')->count();
    }

    /**
     * Do action update
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\View\ItemsList\Model\Product\Admin\Search();
        $list->processQuick();
    }

    /**
     * Do action search
     *
     * @return void
     */
    protected function doActionSearch()
    {
        \XLite\Core\Session::getInstance()
            ->{\XLite\View\ItemsList\Model\Product\Admin\Search::getSessionCellName()} = $this->getSearchParams();

        $this->setReturnURL($this->getURL(array('mode' => 'search', 'searched' => 1)));
    }

    /**
     * Process 'no action'
     *
     * @return void
     */
    protected function doNoAction()
    {
        parent::doNoAction();

        if (\XLite\Core\Request::getInstance()->fast_search) {

            // Refresh search parameters from the request
            \XLite\Core\Session::getInstance()
                ->{\XLite\View\ItemsList\Model\Product\Admin\Search::getSessionCellName()} = $this->getSearchParams();

            // Get ItemsList widget
            $widget = new \XLite\View\ItemsList\Model\Product\Admin\Search();

            // Search for single product entity
            $entity = $widget->searchForSingleEntity();

            if ($entity && $entity instanceOf \XLite\Model\Product) {
                // Prepare redirect to product page
                $url = $this->buildURL('product', '', array('product_id' => $entity->getProductId()));
                $this->setReturnURL($url);
            }
        }
    }

    /**
     * Do action clone
     *
     * @return void
     */
    protected function doActionClone()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            $products = \XLite\Core\Database::getRepo('XLite\Model\Product')->findByIds(array_keys($select));
            if (0 < count($products)) {
                foreach ($products as $product) {
                    $newProduct = $product->cloneEntity();
                    $newProduct->updateQuickData();
                }
                if (1 < count($products)) {
                    $this->setReturnURL($this->buildURL('cloned_products'));

                } else {
                    $this->setReturnURL($this->buildURL('product', '', array('product_id' => $newProduct->getId())));
                }
            }

        } else {
            \XLite\Core\TopMessage::addWarning('Please select the products first');
        }
    }

    /**
     * Do action enable
     *
     * @return void
     */
    protected function doActionEnable()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            \XLite\Core\Database::getRepo('\XLite\Model\Product')->updateInBatchById(
                array_fill_keys(
                    array_keys($select),
                    array('enabled' => true)
                )
            );
            \XLite\Core\TopMessage::addInfo(
                'Products information has been successfully updated'
            );

        } else {
            \XLite\Core\TopMessage::addWarning('Please select the products first');
        }
    }

    /**
     * Do action disable
     *
     * @return void
     */
    protected function doActionDisable()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            \XLite\Core\Database::getRepo('\XLite\Model\Product')->updateInBatchById(
                array_fill_keys(
                    array_keys($select),
                    array('enabled' => false)
                )
            );
            \XLite\Core\TopMessage::addInfo(
                'Products information has been successfully updated'
            );

        } else {
            \XLite\Core\TopMessage::addWarning('Please select the products first');
        }
    }

    /**
     * Do action delete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            \XLite\Core\Database::getRepo('\XLite\Model\Product')->deleteInBatchById($select);
            \XLite\Core\TopMessage::addInfo(
                'Products information has been successfully deleted'
            );

        } else {
            \XLite\Core\TopMessage::addWarning('Please select the products first');
        }
    }

    /**
     * Return search parameters for product list.
     * It is based on search params from Product Items list viewer
     *
     * @return array
     */
    protected function getSearchParams()
    {
        return $this->getSearchParamsCommon()
            + $this->getSearchParamsCheckboxes();
    }

    /**
     * Return search parameters for product list from Product Items list viewer
     *
     * @return array
     */
    protected function getSearchParamsCommon()
    {
        $productsSearchParams = array();

        foreach (
            \XLite\View\ItemsList\Model\Product\Admin\Search::getSearchParams() as $requestParam
        ) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $productsSearchParams[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        return $productsSearchParams;
    }


    /**
     * Return search parameters for product list given as checkboxes: (0, 1) values
     *
     * @return array
     */
    protected function getSearchParamsCheckboxes()
    {
        $productsSearchParams = array();

        $cBoxFields = array(
            \XLite\View\ItemsList\Model\Product\Admin\Search::PARAM_SEARCH_IN_SUBCATS,
            \XLite\View\ItemsList\Model\Product\Admin\Search::PARAM_BY_TITLE,
            \XLite\View\ItemsList\Model\Product\Admin\Search::PARAM_BY_DESCR,
        );

        foreach ($cBoxFields as $requestParam) {
            $productsSearchParams[$requestParam] = isset(\XLite\Core\Request::getInstance()->$requestParam) ? 1 : 0;
        }

        return $productsSearchParams;
    }


    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $searchParams = \XLite\Core\Session::getInstance()
            ->{\XLite\View\ItemsList\Model\Product\Admin\Search::getSessionCellName()};

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }
}
