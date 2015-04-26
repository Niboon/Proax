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
 * HTTPS settings page controller
 */
class HttpsSettings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('HTTPS settings');
    }

    /**
     * Enable HTTPS
     *
     * @return void
     */
    public function doActionEnableHTTPS()
    {
        $this->switchHTTPS(true);

        \XLite\Core\TopMessage::addInfo('HTTPS option has been successfully enabled');

        $this->setReturnURL($this->buildURL($this->get('target')));
    }

    /**
     * Disable HTTPS
     *
     * @return void
     */
    public function doActionDisableHTTPS()
    {
        $this->switchHTTPS(false);

        \XLite\Core\TopMessage::addInfo('HTTPS option has been disabled');

        $this->setReturnURL($this->buildURL($this->get('target')));
    }

    /**
     * Switch HTTPS options
     *
     * @param boolean $enableHTTPS Value
     *
     * @return void
     */
    protected function switchHTTPS($enableHTTPS)
    {
        $options = array(
            'customer_security',
            'admin_security',
        );

        foreach ($options as $option) {
            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                array(
                    'name'     => $option,
                    'category' => 'Security',
                    'value'    => $enableHTTPS,
                )
            );
        }
    }
}
