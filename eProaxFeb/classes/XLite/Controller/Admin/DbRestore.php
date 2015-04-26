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
 * Controller for Database restore page
 * :TODO: must be completly refactored
 */
class DbRestore extends \XLite\Controller\Admin\Base\BackupRestore
{
    /**
     * doActionRestoreFromUploadedFile
     *
     * @return void
     * @throws
     */
    protected function doActionRestoreFromUploadedFile()
    {
        // Check uploaded file with SQL data
        if (isset($_FILES['userfile']) && !empty($_FILES['userfile']['tmp_name']) ) {

            $sqlFile = LC_DIR_TMP . sprintf('sqldump.uploaded.%d.sql', \XLite\Core\Converter::time());

            $tmpFile = $_FILES['userfile']['tmp_name'];

            if (!move_uploaded_file($tmpFile, $sqlFile)) {
                throw new \Exception(static::t('Error of uploading file.'));
            }

            $this->restoreDatabase($sqlFile);

            // Remove source SQL-file if it was uploaded
            unlink($sqlFile);

            
            $this->redirect();
            // Do not update session, etc.
            exit (0);
        }
    }

    /**
     * doActionRestoreFromLocalFile
     *
     * @return void
     */
    protected function doActionRestoreFromLocalFile()
    {
        if (file_exists($this->sqldumpFile)) {
            $this->restoreDatabase($this->sqldumpFile);
            
            $this->redirect();
            
            // Do not update session, etc.
            exit (0);
        }
    }

    /**
     * Common restore database method used by actions
     *
     * @param mixed $sqlFile File with SQL data for loading into database
     *
     * @return boolean
     */
    protected function restoreDatabase($sqlFile)
    {
        $result = false;

        // File to create temporary backup to be able rollback database
        $backupSQLFile = LC_DIR_BACKUP . sprintf('sqldump.backup.%d.sql', \XLite\Core\Converter::time());

        // Make the process of restoring database verbose
        $verbose = true;

        // Start

        $this->startDump();

        // Making the temporary backup file
        \Includes\Utils\Operator::flush(static::t('Making backup of the current database state ... '), true);

        $result = \XLite\Core\Database::getInstance()->exportSQLToFile($backupSQLFile, $verbose);

        \Includes\Utils\Operator::flush(static::t('done') . LC_EOL . LC_EOL, true);

        // Loading specified SQL-file to the database
        \Includes\Utils\Operator::flush(static::t('Loading the database from file .'));

        $result = \Includes\Utils\Database::uploadSQLFromFile($sqlFile, $verbose);
        $restore = false;

        if ($result) {
            // If file has been loaded into database successfully
            $message = static::t('Database restored successfully!');

            // Prepare the cache rebuilding
            \XLite::setCleanUpCacheFlag(true);

        } else {
            // If an error occured while loading file into database
            $message = static::t('The database has not been restored because of the errors');

            $restore = true;
        }

        // Display the result message
        \Includes\Utils\Operator::flush(
            ' ' . static::t('done') . LC_EOL
            . LC_EOL
            . $message . LC_EOL
        );

        if ($restore) {
            // Restore database from temporary backup
            \Includes\Utils\Operator::flush(LC_EOL . static::t('Restoring database from the backup .'));

            \Includes\Utils\Database::uploadSQLFromFile($backupSQLFile, $verbose);
            \Includes\Utils\Operator::flush(' ' . static::t('done') . LC_EOL . LC_EOL);
        }

        // Display Javascript to cancel scrolling page to bottom
        func_refresh_end();

        // Display the bottom HTML part
        $this->displayPageFooter();

        // Remove temporary backup file
        unlink($backupSQLFile);
        
        return $result;
    }

    /**
     * Get redirect mode - force redirect or not
     *
     * @return boolean
     */
    protected function getRedirectMode()
    {
        return true;
    }    
    
    /**
     * getPageReturnURL
     *
     * @return array
     */
    protected function getPageReturnURL()
    {
        $url = array();

        switch (\XLite\Core\Request::getInstance()->action) {

            case 'restore_from_uploaded_file':
            case 'restore_from_local_file':
                $url[] = '<a href="' . $this->buildURL('db_restore') . '">' . static::t('Return to admin interface') . '</a>';
                break;

            default:
                $url = parent::getPageReturnURL();
        }

        return $url;
    }
}
