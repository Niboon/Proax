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
 * Payment method selection  controller
 */
class PaymentMethodSelection extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('search'));
    }

    /**
     * Constructor
     *
     * @param array $params Constructor parameters
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $cellName = \XLite\View\ItemsList\Model\Payment\OnlineMethods::getSessionCellName();
        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();
    }

    /**
     * Get session cell name for pager widget
     *
     * @return string
     */
    public function getPagerSessionCell()
    {
        return parent::getPagerSessionCell() . '_' . md5(microtime(true));
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        switch ($this->getPaymentType()) {
            case \XLite\Model\Payment\Method::TYPE_ALTERNATIVE:
                $result = static::t('Add alternative payment method');
                break;

            case \XLite\Model\Payment\Method::TYPE_OFFLINE:
                $result = static::t('Add offline payment method');
                break;

            default:
                $result = static::t('Add payment method');
                break;
        }

        return $result;
    }

    /**
     * Return payment methods type which is provided to the widget
     *
     * @return string
     */
    protected function getPaymentType()
    {
        return \XLite\Core\Request::getInstance()->{\XLite\View\Button\Payment\AddMethod::PARAM_PAYMENT_METHOD_TYPE};
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $cellName = \XLite\View\ItemsList\Model\Payment\OnlineMethods::getSessionCellName();

        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        $searchParams = $this->getConditions();

        foreach (\XLite\View\ItemsList\Model\Payment\OnlineMethods::getSearchParams() as $requestParam) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $searchParams[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        return $searchParams;
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
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $cellName = \XLite\View\ItemsList\Model\Payment\OnlineMethods::getSessionCellName();
        $searchParams = \XLite\Core\Session::getInstance()->$cellName;

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }
}
