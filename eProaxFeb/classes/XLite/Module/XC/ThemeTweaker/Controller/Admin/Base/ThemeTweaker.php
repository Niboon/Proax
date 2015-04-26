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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\ThemeTweaker\Controller\Admin\Base;

/**
 * CustomJavaScript controller
 */
abstract class ThemeTweaker extends \XLite\Controller\Admin\AAdmin
{
    /**
     * FIXME- backward compatibility
     *
     * @var   array
     */
    protected $params = array('target');

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Look & Feel';
    }

    /**
     * Get file content
     *
     * @return string
     */
    public function getFileContent()
    {
        return \Includes\Utils\FileManager::read($this->getFileName());
    }

    /**
     * Get backup name
     *
     * @return string
     */
    public function getBackupName()
    {
        return 'backup_' . \XLite\Core\Request::getInstance()->target;
    }

    /**
     * Get backup content
     *
     * @return string
     */
    public function getBackupContent()
    {
        return \XLite\Core\Config::getInstance()->XC->ThemeTweaker->{$this->getBackupName()};
    }

    /**
     * Get file name
     *
     * @return string
     */
    protected function getFileName()
    {
        return \XLite\Module\XC\ThemeTweaker\Main::getThemeDir()
                . str_replace('_', '.', \XLite\Core\Request::getInstance()->target);
    }

    /**
     * Restore from backup
     *
     * @return void
     */
    protected function doActionRestore()
    {
        $this->saveCode($this->getBackupContent());
    }

    /**
     * Save
     *
     * @return void
     */
    protected function doActionSave()
    {
        $setting = \XLite\Core\Database::getRepo('XLite\Model\Config')->findOneBy(
            array(
                'name' => 'use_' . \XLite\Core\Request::getInstance()->target,
                'category' => 'XC\\ThemeTweaker'
            )
        );

        \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
            $setting,
            array('value' => isset(\XLite\Core\Request::getInstance()->use))
        );

        $this->saveCode(\XLite\Core\Request::getInstance()->code);
    }

    /**
     * Save
     *
     * @param string $code Code
     *
     * @return void
     */
    protected function saveCode($code)
    {
        if ("\r\n" != PHP_EOL) {
            $code = str_replace("\r\n", PHP_EOL, $code);
        }
        $code = str_replace(chr(194) . chr(160), ' ', $code);

        \Includes\Utils\FileManager::write($this->getFileName(), $code);

        if (\Includes\Utils\FileManager::isFileWriteable($this->getFileName())) {
            \XLite\Core\TopMessage::addInfo('Your custom file is successfully saved');

            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                array(
                    'name'     => $this->getBackupName(),
                    'value'    => $code,
                    'category' => 'XC\\ThemeTweaker',
                )
            );
            \XLite\Core\Config::updateInstance();

            $config = \XLite\Core\Config::getInstance()->Performance;

            if (
                $config->aggregate_css
                || $config->aggregate_js
            ) {
                \Includes\Utils\FileManager::unlinkRecursive(LC_DIR_CACHE_RESOURCES);
                \XLite\Core\TopMessage::addInfo('Aggregation cache has been cleaned');
            }

        } else {
            \XLite\Core\TopMessage::addError(
                'The file {{file}} does not exist or is not writable.',
                array(
                    'file' => $this->getFileName()
                )
            );
        }
    }
}
