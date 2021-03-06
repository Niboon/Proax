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
 * Log getter controller
 */
class Log extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess() && $this->getLogPath();
    }

    /**
     * Get log path
     *
     * @return string
     */
    public function getLogPath()
    {
        $path = \XLite\Core\Request::getInstance()->log;
        if ($path && !preg_match(\XLite\Logger::LOG_FILE_NAME_PATTERN, $path)) {
            $path = null;
        }
        $path = $path ? (LC_DIR_LOG . $path) : null;

        return (!$path || !file_exists($path) || !is_readable($path)) ? null : $path;
    }

    /**
     * Preprocessor for no-action ren
     *
     * @return void
     */
    protected function doNoAction()
    {
        $this->silent = true;

        $path = $this->getLogPath();

        header('Content-Length: ' . filesize($path));
        header('Content-Type: text/plain');
        header(
            'Content-Disposition: attachment;'
            . ' filename="' . substr(basename($path), 0, -4) . '.txt";'
            . ' modification-date="' . date('r', filemtime($path)) . ';'
        );

        readfile($path);
    }

}
