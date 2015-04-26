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
 * Get widget (AJAX)
 * TODO: multiple inheritance required;
 * todo: deprecated, check and remove
 */
class GetWidget extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Handles the request. Parses the request variables if necessary. Attempts to call the specified action function
     *
     * @return void
     */
    public function handleRequest()
    {
        $request = \XLite\Core\Request::getInstance();

        foreach ($this->getAJAXParamsTranslationTable() as $ajaxParam => $requestParam) {
            if (!empty($request->$ajaxParam)) {
                $request->$requestParam = $request->$ajaxParam;
                $this->set($requestParam, $request->$ajaxParam);
            }
        }

        parent::handleRequest();
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess()
            && $this->checkRequest()
            && \XLite\Core\Operator::isClassExists($this->getClass());
    }

    /**
     * Return Viewer object
     *
     * @return \XLite\View\Controller
     */
    public function getViewer($isExported = false)
    {
        return parent::getViewer(true);
    }

    /**
     * Get class name
     *
     * @return string
     */
    public function getClass()
    {
        return \XLite\Core\Request::getInstance()->{self::PARAM_AJAX_CLASS};
    }
    /**
     * These params from AJAX request will be translated into the corresponding ones
     *
     * @return array
     */
    protected function getAJAXParamsTranslationTable()
    {
        return array(
            self::PARAM_AJAX_TARGET => 'target',
            self::PARAM_AJAX_ACTION => 'action',
        );
    }


    /**
     * checkRequest
     *
     * @return boolean
     */
    protected function checkRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH'];
    }

    /**
     * Select template to use
     *
     * @return string
     */
    protected function getViewerTemplate()
    {
        return 'get_widget.tpl';
    }
}
