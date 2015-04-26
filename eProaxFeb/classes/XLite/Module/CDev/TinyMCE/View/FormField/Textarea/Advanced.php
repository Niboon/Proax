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

namespace XLite\Module\CDev\TinyMCE\View\FormField\Textarea;

/**
 * TinyMCE textarea widget
 */
class Advanced extends \XLite\View\FormField\Textarea\Advanced implements \XLite\Base\IDecorator
{
    /**
     * getJSFiles
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = $this->getDir() . '/js/init.tinymce.js';
        $list[] = $this->getDir() . '/js/tinymce/tinymce.min.js';
        $list[] = $this->getDir() . '/js/script.js';

        return $list;
    }

    /**
     * Return CSS files for this widget
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/css/style.css';

        return $list;
    }


    /**
     * getFieldTemplate
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return '/form_field/textarea.tpl';
    }


    /**
     * getDir
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/CDev/TinyMCE';
    }

    /**
     * Return structure of configuration for JS TinyMCE library
     *
     * @return array
     */
    protected function getTinyMCEConfiguration()
    {
        $layout = \XLite\Core\Layout::getInstance();
        $themeFiles = $this->getThemeFiles(false);
        $themeFiles = $themeFiles[static::RESOURCE_CSS];
        $themeFilesCSS = array();
        foreach ($themeFiles as $key => $file) {
            if (!is_array($file)) {
                $path = $layout->getResourceWebPath(
                    $file,
                    \XLite\Core\Layout::WEB_PATH_OUTPUT_URL,
                    \XLite::CUSTOMER_INTERFACE
                );

                if ($path) {
                    $themeFilesCSS[] = $this->getShopURL($path, null, array('t' => LC_START_TIME));
                }
            }
        }

        $contentCSS = implode(',', $themeFilesCSS);

        // Base is the web path to the tinymce library directory
        return array(
            'contentCSS'    => $contentCSS,
            'shopURL'       => \XLite\Core\URLManager::getShopURL(
                null,
                \XLite\Core\Request::getInstance()->isHTTPS(),
                array(),
                null,
                false // Remove $xid parameter
            ),
            'shopURLRoot'   => \XLite\Model\Category::WEB_LC_ROOT,
            'bodyClass'     => $this->getParam(static::PARAM_STYLE),
            'base'          => dirname(\XLite\Singletons::$handler->layout->getResourceWebPath(
                $this->getDir() . '/js/tinymce/tiny_mce.js',
                \XLite\Core\Layout::WEB_PATH_OUTPUT_URL
            )) . '/',
        );
    }

    /**
     * Get processed value 
     * 
     * @return string
     */
    protected function getProcessedValue()
    {
        return str_replace(
            \XLite\Model\Category::WEB_LC_ROOT,
            htmlentities(\XLite::getInstance()->getShopURL(null)),
            $this->getValue()
        );
    }
}
