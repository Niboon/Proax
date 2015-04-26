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

namespace XLite\Module\CDev\XMLSitemap\Controller\Admin;

/**
 * Sitemap
 */
class Sitemap extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('XML sitemap');
    }

    /**
     * Get engines
     *
     * @return array
     */
    public function getEngines()
    {
        return array(
            'Google'  => array(
                'title' => 'Google',
                'url'   => 'http://google.com/webmasters/tools/ping?sitemap=%url%',
            ),
            'Bing / Yahoo'    => array(
                'title' => 'Bing / Yahoo',
                'url'   => 'http://www.bing.com/webmaster/ping.aspx?siteMap=%url%',
            ),
        );
    }

    /**
     * Place URL into engine's endpoints
     *
     * @return void
     */
    protected function doActionLocate()
    {
        $engines = \XLite\Core\Request::getInstance()->engines;

        if ($engines) {
            foreach ($this->getEngines() as $key => $engine) {
                if (in_array($key, $engines)) {
                    $url = urlencode(
                        \XLite::getInstance()->getShopURL(
                            \XLite\Core\Converter::buildURL('sitemap', '', array(), \XLite::CART_SELF)
                        )
                    );
                    $url = str_replace('%url%', $url, $engine['url']);
                    $request = new \XLite\Core\HTTP\Request($url);
                    $response = $request->sendRequest();
                    if (200 == $response->code) {
                        \XLite\Core\TopMessage::addInfo(
                            'Site map successfully registred on X',
                            array('engine' => $key)
                        );

                    } else {
                        \XLite\Core\TopMessage::addWarning(
                            'Site map has not been registred in X',
                            array('engine' => $key)
                        );
                    }
                }
            }
        }

        $postedData = \XLite\Core\Request::getInstance()->getData();
        $options    = \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->findBy(array('category' => $this->getOptionsCategory()));
        $isUpdated  = false;

        foreach ($options as $key => $option) {
            $name = $option->getName();
            $type = $option->getType();

            if (isset($postedData[$name]) || 'checkbox' == $type) {
                if ('checkbox' == $type) {
                    $option->setValue(isset($postedData[$name]) ? 'Y' : 'N');

                } else {
                    $option->setValue($postedData[$name]);
                }

                $isUpdated = true;
                \XLite\Core\Database::getEM()->persist($option);
            }
        }

        if ($isUpdated) {
            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Returns shipping options
     *
     * @return array
     */
    public function getOptions()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->findByCategoryAndVisible($this->getOptionsCategory());
    }


    /**
     * Get options category
     *
     * @return string
     */
    protected function getOptionsCategory()
    {
        return 'CDev\XMLSitemap';
    }
}
