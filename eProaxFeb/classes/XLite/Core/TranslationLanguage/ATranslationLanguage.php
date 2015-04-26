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

namespace XLite\Core\TranslationLanguage;

/**
 * Abstract translation language 
 */
abstract class ATranslationLanguage extends \XLite\Base
{
    /**
     * Label handlers (cache)
     *
     * @var   array
     */
    protected $labelHandlers;

    /**
     * Define label handlers
     *
     * @return array
     */
    protected function defineLabelHandlers()
    {
        return array(
            '_X_ items'                   => 'XItemsMinicart',
            'X items in bag'              => 'XItemsInBag',
            'X items'                     => 'XItems',
            'X items available'           => 'XItemsAvailable',
            'Your shopping bag - X items' => 'YourShoppingBagXItems',
            'X modules will be upgraded'  => 'XModulesWillBeUpgraded',
            'X modules will be disabled'  => 'XModulesWillBeDisabled',
        );
    }

    /**
     * Get label handler
     *
     * @param string $name Label name
     *
     * @return string
     */
    public function getLabelHandler($name)
    {
        $handler = null;
        $handlers = $this->getLabelHandlers();

        if (!empty($handlers[$name])) {
            $handler = $handlers[$name];

            if (is_string($handler)) {
                if (method_exists($this, $handler)) {
                    $handler = array($this, $handler);

                } elseif (method_exists($this, 'translateLabel' . ucfirst($handler))) {
                    $handler = array($this, 'translateLabel' . ucfirst($handler));
                }
            }

            if (!is_callable($handler)) {
                $handler = null;
            }
        }

        return $handler;
    }

    /**
     * Get label handlers
     *
     * @return array
     */
    protected function getLabelHandlers()
    {
        if (!isset($this->labelHandlers)) {
            $this->labelHandlers = $this->defineLabelHandlers();
        }

        return $this->labelHandlers;
    }

    // {{{ Label translators

    /**
     * Translate label 'X items' in minicart
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelXItemsMinicart(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('_X_ item', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('_X_ items', $arguments);
    }

    /**
     * Translate label 'X items in bag'
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelXItemsInBag(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('X item in bag', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('X items in bag', $arguments);
    }

    /**
     * Translate label 'X items'
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelXItems(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('X item', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('X items', $arguments);
    }

    /**
     * Translate label 'X items available'
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelXItemsAvailable(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('X item available', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('X items available', $arguments);
    }

    /**
     * Translate label 'Your shopping bag - X items'
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelYourShoppingBagXItems(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('Your shopping bag - X item', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('Your shopping bag - X items', $arguments);
    }

    /**
     * Translate label 'X modules will be upgraded'
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelXModulesWillBeUpgraded(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('X module will be upgraded', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('X modules will be upgraded', $arguments);
    }

    /**
     * Translate label 'X modules will be disabled'
     *
     * @param array $arguments Arguments
     *
     * @return string
     */
    public function translateLabelXModulesWillBeDisabled(array $arguments)
    {
        return 1 == $arguments['count']
            ? \XLite\Core\Translation::getInstance()->translateByString('X module will be disabled', $arguments)
            : \XLite\Core\Translation::getInstance()->translateByString('X modules will be disabled', $arguments);
    }

    // }}}
}
