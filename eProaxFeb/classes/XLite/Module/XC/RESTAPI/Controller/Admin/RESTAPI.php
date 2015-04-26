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

namespace XLite\Module\XC\RESTAPI\Controller\Admin;

/**
 * REST API controller
 */
class RESTAPI extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Allowed REST methods 
     * 
     * @var array
     */
    protected $allowedMethods = array('GET', 'POST', 'PUT', 'DELETE');

    /**
     * Cached method 
     * 
     * @var string
     */
    protected $cachedMethod;

    /**
     * Handles the request.
     * Parses the request variables if necessary. Attempts to call the specified action function
     *
     * @return void
     */
    public function handleRequest()
    {
        $this->processPut();
        $this->set('silent', true);

        parent::handleRequest();
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess()
            && $this->isRESTRequestAllowed()
            && $this->getSchema()
            && $this->getSchema()->isValid()
            && $this->getPrinter();
    }

    /**
     * Process request
     *
     * @return void
     */
    public function processRequest()
    {
    }

    /**
     * Mark controller run thread as access denied
     *
     * @return void
     */
    protected function markAsAccessDenied()
    {
        if ($this->getSchema() && $this->getSchema()->isForbid()) {
            header('HTTP/1.0 403 Forbidden', true, 403);

        } else {
            header('HTTP/1.0 404 Not Found', true, 404);
        }

        $this->setSuppressOutput(true);
        $this->silence = true;
    }

    /**
     * Process PUT request
     * 
     * @return void
     */
    protected function processPut()
    {
        if ('PUT' == $_SERVER['REQUEST_METHOD']) {
            $str = '';
            $s = fopen('php://input', 'r');
            while ($kb = fread($s, 1024)) {
                $str .= $kb;
            }
            fclose($s);

            $arr = array();
            parse_str($str, $arr);

            if ($arr) {
                \XLite\Core\Request::getInstance()->mapRequest($arr);
            }
        }
    }

    /**
     * Check - REST request is allowed or not
     * 
     * @return boolean
     */
    protected function isRESTRequestAllowed()
    {
        $config = \XLite\Core\Config::getInstance()->XC->RESTAPI;
        $key = \XLite\Core\Request::getInstance()->_key;

        return ($config->key && $config->key == $key)
            || (!$this->isWriteMethod() && ($config->key_read && $config->key_read == $key));
    }

    /**
     * Check - is current place public or not
     *
     * @return boolean
     */
    protected function isPublicZone()
    {
        return true;
    }

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        try {
            $data = $this->getSchema()->process();

        } catch (\Exception $e) {
            header('HTTP/1.0 400 Bad Request', true, 400);
            header('X-REST-Error: ' . $e->getMessage());
            \XLite\Logger::getInstance()->registerException($e);
            $data = null;
        }

        $this->printRESTRequest($data);
    }

    // {{{ Schema

    /**
     * Gt schema 
     * 
     * @return \XLite\Module\XC\RESTAPI\Core\Schema\ASchema
     */
    protected function getSchema()
    {
        if (!isset($this->schema)) {
            $this->schema = $this->defineSchema();
        }

        return $this->schema;
    }

    /**
     * Define schema 
     * 
     * @return \XLite\Module\XC\RESTAPI\Core\Schema\ASchema
     */
    protected function defineSchema()
    {
        $schema = null;
        $code = \XLite\Core\Request::getInstance()->_schema;
        foreach ($this->getSchemaClasses() as $class) {
            if ($class::isOwn($code)) {
                $schema = new $class(\XLite\Core\Request::getInstance(), $this->getMethod());
                break;
            }
        }

        return $schema;
    }

    /**
     * Get schema classes 
     * 
     * @return array
     */
    protected function getSchemaClasses()
    {
        return array(
            '\XLite\Module\XC\RESTAPI\Core\Schema\Native',
            '\XLite\Module\XC\RESTAPI\Core\Schema\Complex',
        );
    }

    // }}}

    // {{{ Common routines

    /**
     * Get method 
     * 
     * @return string
     */
    protected function getMethod()
    {
        if (!isset($this->cachedMethod)) {
            if (!empty(\XLite\Core\Request::getInstance()->_method)) {
                $this->cachedMethod = strtoupper(trim(\XLite\Core\Request::getInstance()->_method));

            } elseif (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->cachedMethod = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);

            } else {
                $this->cachedMethod = strtoupper(\XLite\Core\Request::getInstance()->getRequestMethod());
            }

            if (!in_array($this->cachedMethod, $this->allowedMethods)) {
                $this->cachedMethod = $this->allowedMethods[0];
            }
        }

        return $this->cachedMethod;
    }

    /**
     * Check - is write method or not
     * 
     * @return boolean
     */
    protected function isWriteMethod()
    {
        return in_array($this->getMethod(), array('POST', 'PUT', 'DELETE'));
    }

    // }}}

    // {{{ Output

    /**
     * Get printer 
     * 
     * @return \XLite\Module\XC\RESTAPI\Core\Printer\APrinter
     */
    protected function getPrinter()
    {
        if (!isset($this->printer)) {
            $this->printer = $this->definePrinter();
        }

        return $this->printer;
    }

    /**
     * Define printer
     *
     * @return \XLite\Module\XC\RESTAPI\Core\Printer\APrinter
     */
    protected function definePrinter()
    {
        $printer = null;
        foreach ($this->getPrinterClasses() as $class) {
            if ($class::isOwn()) {
                $printer = new $class($this->getSchema());
                break;
            }
        }

        return $printer;
    }

    /**
     * Get printer classes
     *
     * @return array
     */
    protected function getPrinterClasses()
    {
        return array(
            '\XLite\Module\XC\RESTAPI\Core\Printer\XML',
            '\XLite\Module\XC\RESTAPI\Core\Printer\YAML',
            '\XLite\Module\XC\RESTAPI\Core\Printer\JSONP',
            '\XLite\Module\XC\RESTAPI\Core\Printer\JSON',
        );
    }

    /**
     * Print REST request 
     * 
     * @param mixed $data Data
     *  
     * @return void
     */
    protected function printRESTRequest($data)
    {
        $this->getPrinter()->printOutput($data);
    }

    // }}}

}

