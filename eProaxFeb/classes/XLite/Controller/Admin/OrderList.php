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
 * Orders list controller
 */
class OrderList extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL() || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage orders');
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Search for orders');
    }

    // {{{ Search

    /**
     * getDateValue
     * FIXME - to remove
     *
     * @param string  $fieldName Field name (prefix)
     * @param boolean $isEndDate End date flag OPTIONAL
     *
     * @return integer
     */
    public function getDateValue($fieldName, $isEndDate = false)
    {
        $dateValue = \XLite\Core\Request::getInstance()->$fieldName;

        if (!isset($dateValue)) {
            $nameDay   = $fieldName . 'Day';
            $nameMonth = $fieldName . 'Month';
            $nameYear  = $fieldName . 'Year';

            if (
                isset(\XLite\Core\Request::getInstance()->$nameMonth)
                && isset(\XLite\Core\Request::getInstance()->$nameDay)
                && isset(\XLite\Core\Request::getInstance()->$nameYear)
            ) {
                $dateValue = mktime(
                    $isEndDate ? 23 : 0,
                    $isEndDate ? 59 : 0,
                    $isEndDate ? 59 : 0,
                    \XLite\Core\Request::getInstance()->$nameMonth,
                    \XLite\Core\Request::getInstance()->$nameDay,
                    \XLite\Core\Request::getInstance()->$nameYear
                );
            }
        }

        return $dateValue;
    }

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
     * Get date condition parameter (start or end)
     *
     * @param boolean $start Start date flag, otherwise - end date  OPTIONAL
     *
     * @return mixed
     */
    public function getDateCondition($start = true)
    {
        $dates = $this->getCondition(\XLite\Model\Repo\Order::P_DATE);
        $n = (true === $start) ? 0 : 1;

        return isset($dates[$n]) ? $dates[$n] : null;
    }

    /**
     * Common prefix for editable elements in lists
     *
     * NOTE: this method is requered for the GetWidget and AAdmin classes
     * TODO: after the multiple inheritance should be moved to the AAdmin class
     *
     * @return string
     */
    public function getPrefixPostedData()
    {
        return 'data';
    }

    /**
     * Define the session cell name for the order list
     *
     * @return string
     */
    protected function getSessionCellName()
    {
        return \XLite\View\ItemsList\Model\Order\Admin\Search::getSessionCellName();
    }

    /**
     * Define the search params
     *
     * @return array
     */
    protected function getSearchParams()
    {
        return \XLite\View\ItemsList\Model\Order\Admin\Search::getSearchParams();
    }

    /**
     * Define the items list class
     *
     * @return \XLite\View\ItemsList\Model\Order\Admin\Search
     */
    protected function getItemsList()
    {
        $list = new \XLite\View\ItemsList\Model\Order\Admin\Search();

        return $list;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $searchParams = \XLite\Core\Session::getInstance()->{$this->getSessionCellName()};

        return is_array($searchParams) ? $searchParams : array();
    }

    // }}}

    // {{{ Actions

    /**
     * Search by customer
     *
     * @return void
     */
    protected function doActionSearchByCustomer()
    {
        \XLite\Core\Session::getInstance()->{$this->getSessionCellName()} = array(
            'substring' => \XLite\Core\Request::getInstance()->substring,
            'profileId' => intval(\XLite\Core\Request::getInstance()->profileId),
        );

        $this->setReturnURL($this->getURL(array('searched' => 1)));
    }

    /**
     * doActionUpdate
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $changes = $this->getOrdersChanges();

        $this->getItemsList()->processQuick();

        foreach ($changes as $orderId => $change) {
            \XLite\Core\OrderHistory::getInstance()->registerOrderChanges($orderId, $change);
        }
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $this->prepareSearchParams();

        $this->setReturnURL($this->getURL(array('searched' => 1)));
    }

    /**
     * Clear search conditions
     *
     * @return void
     */
    protected function doActionClearSearch()
    {
        \XLite\Core\Session::getInstance()->{$this->getSessionCellName()} = array();

        $this->setReturnURL($this->getURL(array('searched' => 1)));
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

            // Clear stored search conditions
            \XLite\Core\Session::getInstance()->{$this->getSessionCellName()} = array();
            $this->prepareSearchParams();

            // Get ItemsList widget
            $widget = new \XLite\View\ItemsList\Model\Order\Admin\Search();

            // Search for single order
            $entity = $widget->searchForSingleEntity();

            if ($entity && $entity instanceOf \XLite\Model\Order) {
                // Prepare redirect to order page
                $url = $this->buildURL('order', '', array('order_number' => $entity->getOrderNumber()));
                $this->setReturnURL($url);
            }
        }
    }

    /**
     * Initialize search parameters from request data
     *
     * @return void
     */
    protected function prepareSearchParams()
    {
        $ordersSearch = array();

        // Prepare dates
        $this->startDate = $this->getDateValue('startDate');
        $this->endDate   = $this->getDateValue('endDate', true);

        if (
            0 === $this->startDate
            || 0 === $this->endDate
            || $this->startDate > $this->endDate
        ) {
            $date = getdate(\XLite\Core\Converter::time());

            $this->startDate = mktime(0, 0, 0, $date['mon'], 1, $date['year']);
            $this->endDate   = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
        }

        foreach ($this->getSearchParams() as $modelParam => $requestParam) {
            if (\XLite\Model\Repo\Order::P_DATE === $requestParam) {
                $ordersSearch[$requestParam] = array($this->startDate, $this->endDate);

            } elseif (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $ordersSearch[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        if (!isset($ordersSearch[\XLite\Model\Repo\Order::P_PROFILE_ID])) {
            $ordersSearch[\XLite\Model\Repo\Order::P_PROFILE_ID] = 0;
        }

        \XLite\Core\Session::getInstance()->{$this->getSessionCellName()} = $ordersSearch;
    }

    /**
     * Get order changes from request
     *
     * @return array
     */
    protected function getOrdersChanges()
    {
        $changes = array();

        foreach ($this->getPostedData() as $orderId => $data) {
            $order = \XLite\Core\Database::getRepo('XLite\Model\Order')->find($orderId);

            foreach ($data as $name => $value) {
                if ('status' === $name) {
                    continue;
                }

                $dataFromOrder = $order->{'get' . ucfirst($name)}();

                if ($dataFromOrder->getId() !== intval($value)) {
                    $changes[$orderId][$name] = array(
                        'old' => $dataFromOrder,
                        'new' => $value,
                    );
                }
            }
        }

        return $changes;
    }

    // }}}
}
