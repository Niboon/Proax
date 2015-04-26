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

namespace XLite\Module\CDev\Paypal\View;

/**
 * Paypal banner
 *
 */
class Banner extends \XLite\View\Dialog
{
    const PARAM_POSITION = 'position';
    const PARAM_PAGE     = 'page';

    /**
     * Payment method
     *
     * @var \XLite\Model\Payment\Method
     */
    protected $method = null;

    /**
     * Get css files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Get publisher id
     *
     * @return string
     */
    protected function getPublisherId()
    {
        return $this->getSetting('publisherId');
    }

    /**
     * Get placement type
     *
     * @return string
     */
    protected function getPlacementType()
    {
        $placementType = '800x66';

        if ('productDetailsPages' == $this->getParam(static::PARAM_PAGE)) {
            $placementType = '468x60';
        }

        if ('cartPage' == $this->getParam(static::PARAM_PAGE)) {
            $placementType = '234x60';
        }

        return $placementType;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/CDev/Paypal/banner';
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getCSSClasses()
    {
        return $this->getParam(static::PARAM_PAGE)
            . ' ' . ' position-' . $this->getParam(static::PARAM_POSITION);
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
            static::PARAM_PAGE     => new \XLite\Model\WidgetParam\String('Page', ''),
            static::PARAM_POSITION => new \XLite\Model\WidgetParam\String('Position', ''),
        );
    }

    /**
     * Get payment method
     *
     * @return \XLite\Model\Payment\Method
     */
    protected function getMethod()
    {
        if (is_null($this->method)) {
            $this->method = \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')
                ->findOneBy(array('service_name' => 'PaypalCredit'));
        }

        return $this->method;
    }

    /**
     * Get Paypal credit setting
     *
     * @param string $name Setting name
     *
     * @return string
     */
    protected function getSetting($name)
    {
        return $this->getMethod()->getSetting($name);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible()
            && (
                \XLite\Module\CDev\Paypal\Main::isPaypalCreditEnabled()
                || \XLite\Module\CDev\Paypal\Main::isPaypalWPSEnabled()
            )
            && $this->isValidTarget()
            && $this->getPublisherId()
            && $this->getSetting('agreement');

        if ($result) {
            $settingName = 'bannerOn' . ucfirst($this->getParam(static::PARAM_PAGE));

            $result = $this->getParam(static::PARAM_POSITION) == $this->getSetting($settingName);
        }

        return $result;
    }

    /**
     * Is valid target
     *
     * @return boolean
     */
    protected function isValidTarget()
    {
        $target = $this->getTarget();

        switch ($this->getParam(static::PARAM_PAGE)) {
            case 'homePage':
                $result = 'main' == $target;
                break;

            case 'categoryPages':
                $result = 'category' == $target;
                break;

            case 'productDetailsPages':
                $result = 'product' == $target;
                break;

            case 'cartPage':
                $result = 'cart' == $target;
                break;

            default:
                $result = false;
                break;
        }

        return $result;
    }
}
