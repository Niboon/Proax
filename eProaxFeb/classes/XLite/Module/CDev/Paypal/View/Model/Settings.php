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

namespace XLite\Module\CDev\Paypal\View\Model;

/**
 * Settings dialog model widget
 */
abstract class Settings extends \XLite\View\Model\Settings implements \XLite\Base\IDecorator
{
    /**
     * Get schema fields
     *
     * @return array
     */
    public function getSchemaFields()
    {
        return $this->preparePaypalOptions(parent::getSchemaFields());
    }

    /**
     * Get editable options
     *
     * @return array
     */
    protected function getEditableOptions()
    {
        return $this->preparePaypalOptions(parent::getEditableOptions());
    }

    /**
     * Prepare options
     *
     * @param array $options Options
     *
     * @return array
     */
    protected function preparePaypalOptions(array $options)
    {
        if (
            'module' == $this->getTarget()
            && $this->getModule()
            && 'CDev\Paypal' == $this->getModule()->getActualName()
        ) {
            foreach ($options as $name => $option) {
                if ('show_admin_welcome' == $name) {
                    unset($options[$name]);
                }
            }
        }

        return $options;
    }
}

