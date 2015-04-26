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

namespace XLite\Module\XC\ThemeTweaker\View\FormField\Label;

/**
 * Label
 */
class TemplatesChain extends \XLite\View\FormField\Label\ALabel
{
    /**
     * Return name of the folder with templates
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ThemeTweaker/form_field/templates_chain';
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'body.tpl';
    }

    /**
     * Assemble classes
     *
     * @param array $classes Classes
     *
     * @return array
     */
    protected function assembleClasses(array $classes)
    {
        $classes = parent::assembleClasses($classes);
        $classes[] = 'templates-chain-field';

        return $classes;
    }

    /**
     * Return templates chain
     *
     * @return array
     */
    protected function getChain()
    {
        $result = array();

        /** @var \XLite\Core\Layout $layout */
        $layout = \XLite\Core\Layout::getInstance();
        $shortPath = $this->getValue();

        $files = array();
        foreach ($layout->getSkinPaths(\XLite::CUSTOMER_INTERFACE) as $path) {
            $fullPath = $path['fs'] . LC_DS . $shortPath;
            if (file_exists($fullPath) && is_file($fullPath)) {
                array_unshift($files, $fullPath);
            }
        }

        foreach ($files as $fullPath) {
            $result[substr($fullPath, strlen(LC_DIR_SKINS))] = htmlspecialchars(\Includes\Utils\FileManager::read($fullPath));
        }

        return $result;
    }
}
