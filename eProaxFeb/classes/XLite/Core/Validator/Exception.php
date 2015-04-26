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
 * Validate exception
 */
class Exception extends \XLite\Core\Exception
{
    /**
     * Path
     *
     * @var array
     */
    protected $path = array();

    /**
     * Internal flag
     *
     * @var boolean
     */
    protected $internal = null;

    /**
     * Message label arguments
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * Public name
     *
     * @var string
     */
    protected $publicName;

    /**
     * Some additional data
     *
     * @var \XLite\Core\CommonCell
     */
    protected $data;

    /**
     * Add path item
     *
     * @param mixed $item Path item key
     *
     * @return void
     */
    public function addPathItem($item)
    {
        array_unshift($this->path, $item);
    }

    /**
     * Set public name
     *
     * @param string $name Public name
     *
     * @return void
     */
    public function setPublicName($name)
    {
        $this->publicName = $name;
    }

    /**
     * Get public name
     *
     * @return string
     */
    public function getPublicName()
    {
        return $this->publicName;
    }

    /**
     * Get path as string
     *
     * @return string
     */
    public function getPath()
    {
        $path = $this->path[0];

        if (1 < count($this->path)) {
            $path .= '[' . implode('][', array_slice($this->path, 1)) . ']';
        }

        return $path;
    }

    /**
     * Mark exception as internal error exception
     *
     * @return void
     */
    public function markAsInternal()
    {
        $this->internal = true;
    }

    /**
     * Check - exception is internal or not
     *
     * @return boolean
     */
    public function isInternal()
    {
        return $this->internal;
    }

    /**
     * Set message arguments
     *
     * @param array $arguments Arguments
     *
     * @return void
     */
    public function setLabelArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Get message arguments
     *
     * @return array
     */
    public function getLabelArguments()
    {
        return $this->arguments;
    }

    /**
     * Set additional data
     *
     * @param \XLite\Core\CommonCell $data Data
     *
     * @return void
     */
    public function setData(\XLite\Core\CommonCell $data)
    {
        $this->data = $data;
    }

    /**
     * Returns additional data
     *
     * @return \XLite\Core\CommonCell
     */
    public function getData()
    {
        return !isset($this->data)
            ? ($this->data = new \XLite\Core\CommonCell())
            : $this->data;
    }
}
