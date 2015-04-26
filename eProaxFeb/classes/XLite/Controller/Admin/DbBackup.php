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

namespace XLite\Controller\Admin;

/**
 * Controller for Database backup page
 * :TODO: must be completly refactored
 */
class DbBackup extends \XLite\Controller\Admin\Base\BackupRestore
{
    /**
     * doActionBackup
     *
     * @return void
     */
    protected function doActionBackup()
    {
        $destFile = LC_DIR_BACKUP . sprintf('sqldump.backup.%d.sql', \XLite\Core\Converter::time());
        $this->startDownload('db_backup.sql');
        // Make database backup and store it in $this->sqldumpFile file
        \XLite\Core\Database::getInstance()->exportSQLToFile($destFile, false);
        
        readfile($destFile);
        \Includes\Utils\FileManager::deleteFile($destFile);

        exit ();
    }

    /**
     * doActionBackupWriteToFile
     *
     * @return void
     */
    protected function doActionBackupWriteToFile()
    {
        $destFile = $this->sqldumpFile;
        $this->startDump();
        // Make database backup and store it in $this->sqldumpFile file
        \XLite\Core\Database::getInstance()->exportSQLToFile($destFile, true);
        \XLite\Core\TopMessage::addInfo('Database backup created successfully');

        $this->setReturnURL($this->buildURL('db_backup'));
        $this->doRedirect();
    }

    /**
     * doActionDelete
     *
     * @return void
     * @throws
     */
    protected function doActionDelete()
    {
        if (
            \Includes\Utils\FileManager::isExists($this->sqldumpFile)
            && !\Includes\Utils\FileManager::deleteFile($this->sqldumpFile)
        ) {
            \XLite\Core\TopMessage::addError(static::t('Unable to delete file') . ' ' . $this->sqldumpFile);
        } else {
            \XLite\Core\TopMessage::addInfo('SQL file was deleted successfully');
        }
        $this->doRedirect();
    }
}
