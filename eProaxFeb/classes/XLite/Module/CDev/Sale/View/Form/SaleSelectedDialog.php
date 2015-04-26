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

namespace XLite\Module\CDev\Sale\View\Form;

/**
 * "Set the sale price" dialog form class
 */
class SaleSelectedDialog extends \XLite\View\Form\AForm
{
    /**
     * getDefaultTarget
     *
     * @return string
     */
    protected function getDefaultTarget()
    {
        return 'sale_selected';
    }

    /**
     * getDefaultAction
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'set_sale_price';
    }

    /**
     * Get validator
     *
     * @return \XLite\Core\Validator\HashArray
     */
    protected function getValidator()
    {
        $validator = parent::getValidator();

        $data = $validator->addPair('postedData', new \XLite\Core\Validator\HashArray());
        $this->setDataValidators($data);

        return $validator;
    }

    /**
     * Set validators pairs for products data
     *
     * @param mixed &$data Data
     *
     * @return void
     */
    protected function setDataValidators(&$data)
    {
        $this->setSaleDataValidators($data);
    }

    /**
     * Called before the includeCompiledFile()
     *
     * @return void
     */
    protected function initView()
    {
        parent::initView();

        $toDelete = array();

        if (is_array(\XLite\Core\Request::getInstance()->select)) {
            foreach (\XLite\Core\Request::getInstance()->select as $productId => $value) {
                $toDelete['select[' . $productId . ']'] = $productId;
            }
        }

        $postedData = array(
            'postedData[participateSale]' => true,
        );

        $this->widgetParams[self::PARAM_FORM_PARAMS]->appendValue($toDelete);
        $this->widgetParams[self::PARAM_FORM_PARAMS]->appendValue($postedData);
    }


}
