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

namespace XLite\Module\CDev\FrTranslation;

/**
 * French translation module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Language code
     */
    const LANG_CODE = 'fr';

    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'X-Cart team';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Translation: French';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.2';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '0';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'French translation pack. '
            . 'This translation is a contribution of X-Cart community. Kindly shared by one of X-Cart merchants for '
            . 'free, this translation may contain some non-translated labels which appeared in later versions of '
            . 'X-Cart 5 or which were added by custom modules you installed.';
    }

    /**
     * Decorator run this method at the end of cache rebuild
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        $language = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::LANG_CODE);

        if (isset($language)) {

            if (!$language->getAdded()) {

                $language->setAdded(true);
                $language->setEnabled(true);

                \XLite\Core\TopMessage::addInfo(
                    'The X language has been added and enabled successfully',
                    array('language' => $language->getName()),
                    $language->getCode()
                );
            }

        } else {
            \XLite\Core\TopMessage::addError('The language you want to add has not been found');
        }
    }

    /**
     * Method to call just before the module is disabled via core
     *
     * @return void
     */
    public static function callDisableEvent()
    {
        parent::callDisableEvent();

        $language = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::LANG_CODE);

        if (isset($language)) {
            $language->setEnabled(false);
            $language->setAdded(false);

            \XLite\Core\Database::getRepo('\XLite\Model\Language')->update($language);
            \XLite\Core\Translation::getInstance()->reset();
        }
    }

    /**
     * Method to call just before the module is uninstalled via core
     *
     * @return void
     */
    public static function callUninstallEvent()
    {
        parent::callUninstallEvent();

        $language = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::LANG_CODE);

        if (isset($language)) {
            $language->setModule(null);

            \XLite\Core\Database::getRepo('\XLite\Model\Language')->update($language);
            \XLite\Core\Translation::getInstance()->reset();
        }
    }

    /**
     * Check if the active current language is German
     *
     * @return boolean
     */
    public static function isActiveLanguage()
    {
        return static::LANG_CODE === \XLite\Core\Session::getInstance()->getLanguage()->getCode();
    }
}
