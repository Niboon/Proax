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

namespace XLite\Core\Validator;

/**
 * Abstract validator
 */
abstract class AValidator extends \XLite\Base\SuperClass
{
    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     */
    abstract public function validate($data);

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sanitize
     *
     * @param mixed $data Daa
     *
     * @return mixed
     */
    public function sanitize($data)
    {
        return $data;
    }

    /**
     * Throw error exception
     *
     * @param string $message    Message
     * @param array  $arguments  Language label arguments OPTIONAL
     * @param mixed  $pathItem   Path item key OPTIONAL
     * @param string $publicName Path item public name OPTIONAL
     *
     * @return \XLite\Core\Validator\Exception
     */
    protected function throwError($message, array $arguments = array(), $pathItem = null, $publicName = null)
    {
        $exception = new \XLite\Core\Validator\Exception($message);
        $exception->setLabelArguments($arguments);

        if (isset($pathItem)) {
            $exception->addPathItem($pathItem);
        }

        if ($publicName) {
            $exception->setPublicName($publicName);
        }

        return $exception;
    }

    /**
     * Throw internal error exception
     *
     * @param string $message   Message
     * @param array  $arguments Language label arguments OPTIONAL
     *
     * @return \XLite\Core\Validator\Exception
     */
    protected function throwInternalError($message, array $arguments = array())
    {
        $exception = new \XLite\Core\Validator\Exception($message);
        $exception->setLabelArguments($arguments);
        $exception->markAsInternal();

        return $exception;
    }
}
