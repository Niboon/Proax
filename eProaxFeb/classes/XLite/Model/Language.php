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

namespace XLite\Model;

/**
 * Language
 *
 * @Entity
 * @Table (name="languages",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="code3", columns={"code3"}),
 *          @UniqueConstraint (name="code2", columns={"code"})
 *      },
 *      indexes={
 *          @Index (name="added", columns={"added"}),
 *          @Index (name="enabled", columns={"enabled"})
 *      }
 * )
 */
class Language extends \XLite\Model\Base\I18n
{
    /**
     * Unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column (type="integer", unique=true)
     */
    protected $lng_id;

    /**
     * Language alpha-2 code (ISO 639-2)
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=2, unique=true)
     */
    protected $code;

    /**
     * Language alpha-3 code (ISO 639-3)
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=3, unique=true)
     */
    protected $code3 = '';

    /**
     * Right-to-left flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $r2l = false;

    /**
     * Language status (added/not added)
     *
     * @var integer
     *
     * @Column (type="boolean")
     */
    protected $added = false;

    /**
     * Language state (enabled/disabled)
     *
     * @var integer
     *
     * @Column (type="boolean")
     */
    protected $enabled = false;

    /**
     * Related module (Author\Name)
     *
     * @var string
     *
     * @Column (type="string", nullable=true)
     */
    protected $module;


    /**
     * Get module
     *
     * @return string
     */
    public function getValidModule()
    {
        $result = null;

        $module = $this->getModule();

        if (!empty($module) && preg_match('/\w+\\\\\w+/', $module)) {
            $class = \Includes\Utils\ModulesManager::getClassNameByModuleName($module);
            if (class_exists($class)) {
                $result = $module;
            }
        }

        return $result;
    }

    /**
     * Return true if current language is set as a default for customer interface
     *
     * @return boolean
     */
    public function getDefaultCustomer()
    {
        return $this->getCode() == \XLite\Core\Config::getInstance()->General->default_language;
    }

    /**
     * Return true if current language is set as a default for admin interface
     *
     * @return boolean
     */
    public function getDefaultAdmin()
    {
        return $this->getCode() == \XLite\Core\Config::getInstance()->General->default_admin_language;
    }

    /**
     * Get flag URL
     *
     * @return string|void
     */
    public function getFlagURL()
    {
        $path = \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'images/flags_svg/' . $this->getCode() . '.svg',
            \XLite\Core\Layout::WEB_PATH_OUTPUT_URL,
            \XLite\Core\Layout::PATH_COMMON
        );

        if (!$path) {
            $path = \XLite\Core\Layout::getInstance()->getResourceWebPath(
                'images/flags_svg/__.svg',
                \XLite\Core\Layout::WEB_PATH_OUTPUT_URL,
                \XLite\Core\Layout::PATH_COMMON
            );
        }

        return $path;
    }

    /**
     * Get flag URL
     *
     * @return string|void
     */
    public function getFlagFile()
    {
        $file = 'images/flags_svg/' . $this->getCode() . '.svg';
        $path = \XLite\Core\Layout::getInstance()->getResourceFullPath(
            $file,
            \XLite\Core\Layout::PATH_COMMON
        );

        return $path
            ? $file
            : 'images/flags_svg/__.svg';
    }

    /**
     * Remove all label translations to the language
     * Return true on success
     *
     * @return boolean
     */
    public function removeTranslations()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\LanguageLabel')->deleteTranslations($this->getCode());
    }

    /**
     * Get default language code
     *
     * @return string
     */
    protected function getSessionLanguageCode()
    {
        return $this->getCode();
    }
}
