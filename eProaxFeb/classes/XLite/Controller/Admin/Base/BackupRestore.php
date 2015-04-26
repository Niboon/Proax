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

namespace XLite\Controller\Admin\Base;

/**
 * Base controller for Backup/Restore section
 */
abstract class BackupRestore extends \XLite\Controller\Admin\AAdmin
{
    /**
     * sqldumpFile
     *
     * @var mixed
     */
    protected $sqldumpFile = null;


    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Backup & Restore');
    }

    /**
     * handleRequest
     *
     * @return void
     */
    public function handleRequest()
    {
        if (\XLite\Core\Request::getInstance()->isPost()) {
            set_time_limit(0);
        }
        $this->sqldumpFile = LC_DIR_BACKUP . 'sqldump.sql.php';

        parent::handleRequest();
    }

    /**
     * isFileExists
     *
     * @return boolean
     */
    public function isFileExists()
    {
        return \Includes\Utils\FileManager::isExists($this->sqldumpFile);
    }

    /**
     * isFileWritable
     *
     * @return boolean
     */
    public function isFileWritable()
    {
        return $this->isDirExists()
            && (
                !$this->isFileExists()
                || \Includes\Utils\FileManager::isFileWriteable($this->sqldumpFile)
            );
    }

    /**
     * isDirExists
     *
     * @return boolean
     */
    public function isDirExists()
    {
        if (!is_dir(LC_DIR_BACKUP)) {
            \Includes\Utils\FileManager::mkdirRecursive(LC_DIR_BACKUP);
        }
        return \Includes\Utils\FileManager::isDirWriteable(LC_DIR_BACKUP);
    }
}
