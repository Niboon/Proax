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

namespace XLite\Module\CDev\Paypal\View\Login;

/**
 * Social sign-in widget
 */
class Widget extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_CAPTION     = 'caption';
    const PARAM_TEXT_BEFORE = 'text_before';
    const PARAM_TEXT_AFTER  = 'text_after';
    const PARAM_BUTTON_STYLE = 'buttonStyle';

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/CDev/Paypal/login/style.css';

        return $list;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'modules/CDev/Paypal/login/controller.js';

        return $list;
    }

    /**
     * Return default template
     * See setWidgetParams()
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/Paypal/login/widget.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Module\CDev\Paypal\Core\Login::isConfigured()
            && !\XLite\Core\Auth::getInstance()->isLogged();
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_CAPTION     => new \XLite\Model\WidgetParam\String('Caption', null),
            static::PARAM_TEXT_BEFORE => new \XLite\Model\WidgetParam\String('TextBefore', null),
            static::PARAM_TEXT_AFTER  => new \XLite\Model\WidgetParam\String('TextAfter', null),
            static::PARAM_BUTTON_STYLE
                => new \XLite\Model\WidgetParam\String('Button style', $this->defineButtonStyle()),
        );
    }

    /**
     * Get auth url
     *
     * @return string
     */
    protected function getAuthURL()
    {
        return $this->buildURL(
            'paypal_login',
            '',
            array('state' => get_class(\XLite::getController()))
        );
    }

    /**
     * Get widget caption
     *
     * @return string
     */
    protected function getCaption()
    {
        return $this->getParam(static::PARAM_CAPTION);
    }

    /**
     * Get widget's preceding text
     *
     * @return string
     */
    protected function getTextBefore()
    {
        return $this->getParam(static::PARAM_TEXT_BEFORE);
    }

    /**
     * Get widget's following text
     *
     * @return string
     */
    protected function getTextAfter()
    {
        return $this->getParam(static::PARAM_TEXT_AFTER);
    }

    /**
     * Returns social button style
     *
     * @return string
     */
    protected function getButtonStyle()
    {
        return $this->getParam(static::PARAM_BUTTON_STYLE);
    }

    /**
     * Define default button style
     *
     * @return string
     */
    protected function defineButtonStyle()
    {
        return 'button';
    }
}
