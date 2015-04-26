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
 * Top sellers statistics page controller
 */
class TopSellers extends \XLite\Controller\Admin\Stats
{
    /**
     * Number of positions
     */
    const TOP_SELLERS_NUMBER = 10;

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
     * getPageTemplate
     *
     * @return string
     */
    public function getPageTemplate()
    {
        return 'top_sellers.tpl';
    }

    /**
     * Get rows count in statistics
     *
     * @return integer
     */
    public function getRowsCount()
    {
        return self::TOP_SELLERS_NUMBER;
    }

    /**
     * Get columns for statistics table
     *
     * @return array
     */
    public function getStatsRows()
    {
        return array_keys(array_fill(0, $this->getRowsCount(), ''));
    }

    /**
     * Prepare statistics table
     *
     * @return array
     */
    public function getStats()
    {
        parent::getStats();

        $this->stats = $this->processData($this->getData());

        return $this->stats;
    }

    /**
     * Get data
     *
     * @return array
     */
    protected function getData()
    {
        $data = array();

        foreach ($this->getStatsColumns() as $interval) {
            $condition = $this->defineDetDataCondition($interval);

            $data[$interval] = \XLite\Core\Database::getRepo('\XLite\Model\OrderItem')->getTopSellers($condition);
        }

        return $data;
    }

    /**
     * Define condition fo getData
     *
     * @param string $interval Time interval
     *
     * @return \XLite\Core\CommonCell
     */
    protected function defineDetDataCondition($interval)
    {
        $condition = $this->getSearchCondition($interval);
        $condition->limit = self::TOP_SELLERS_NUMBER;

        $currency = null;

        if (\XLite\Core\Request::getInstance()->currency) {
            $currency = \XLite\Core\Database::getRepo('XLite\Model\Currency')
                ->find(\XLite\Core\Request::getInstance()->currency);
        }

        if (!$currency) {
            $currency = \XLite::getInstance()->getCurrency();
        }

        $condition->currency = $currency->getCurrencyId();

        return $condition;
    }

    /**
     * processData
     *
     * @param array $data Collected data
     *
     * @return array
     */
    protected function processData($data)
    {
        $stats = $this->stats;

        foreach ($this->stats as $rownum => $periods) {

            foreach ($periods as $period => $val) {

                $stats[$rownum][$period] = (
                    is_array($data[$period])
                    && \Includes\Utils\ArrayManager::getIndex($data[$period], $rownum)
                )
                    ? $data[$period][$rownum][0]
                    : null;
            }
        }

        return $stats;
    }
}
