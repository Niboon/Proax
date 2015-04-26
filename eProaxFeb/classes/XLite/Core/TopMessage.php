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

namespace XLite\Core;

/**
 * Top message
 */
class TopMessage extends \XLite\Base\Singleton
{
    /**
     * Message types
     */
    const INFO    = 'info';
    const WARNING = 'warning';
    const ERROR   = 'error';

    /**
     * Message fields
     */
    const FIELD_TEXT   = 'text';
    const FIELD_TYPE   = 'type';
    const FIELD_AJAX   = 'ajax';
    const FIELD_WIDGET = 'widget';

    /**
     * Types list
     *
     * @var array
     */
    protected $types = array(self::INFO, self::WARNING, self::ERROR);

    /**
     * Current messages
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Add information-type message with additional translation arguments
     *
     * @param string $text      Label name
     * @param array  $arguments Substitution arguments OPTIONAL
     * @param string $code      Language code OPTIONAL
     *
     * @return boolean
     */
    public static function addInfo($text, array $arguments = array(), $code = null)
    {
        return static::getInstance()->add($text, $arguments, $code, static::INFO);
    }

    /**
     * Add warning-type message with additional translation arguments
     *
     * @param string $text      Label name
     * @param array  $arguments Substitution arguments OPTIONAL
     * @param string $code      Language code OPTIONAL
     *
     * @return boolean
     */
    public static function addWarning($text, array $arguments = array(), $code = null)
    {
        return static::getInstance()->add($text, $arguments, $code, static::WARNING);
    }

    /**
     * Add error-type message with additional translation arguments
     *
     * @param string $text      Label name
     * @param array  $arguments Substitution arguments OPTIONAL
     * @param string $code      Language code OPTIONAL
     *
     * @return boolean
     */
    public static function addError($text, array $arguments = array(), $code = null)
    {
        return static::getInstance()->add($text, $arguments, $code, static::ERROR);
    }


    /**
     * Add message
     *
     * @param string  $text      Message text
     * @param array   $arguments Substitution arguments OPTIONAL
     * @param string  $code      Language code OPTIONAL
     * @param string  $type      Message type OPTIONAL
     * @param boolean $rawText   Preprocessing text flag OPTIONAL
     * @param boolean $ajax      AJAX message state OPTIONAL
     *
     * @return boolean
     */
    public function add($text, array $arguments = array(), $code = null, $type = self::INFO, $rawText = false, $ajax = true)
    {
        $result = false;

        if (!empty($text)) {

            if (
                is_object($text)
                && $text instanceof \XLite\View\AView
            ) {
                $text = $text->getContent();
                $rawText = true;
            }

            if (!$rawText) {
                $text = static::t($text, $arguments, $code);
            }

            if (!in_array($type, $this->types)) {
                $type = static::INFO;
            }

            // To prevent duplicated messages
            // :TODO: use "array_unique()" instead
            $this->messages[$type . md5($text)] = array(
                static::FIELD_TEXT => $text,
                static::FIELD_TYPE => $type,
                static::FIELD_AJAX => $ajax,
            );

            \XLite\Core\Session::getInstance()->topMessages = $this->messages;

            $result = true;
        }

        return $result;
    }

    /**
     * Add messages
     *
     * @param array  $messages Message texts
     * @param string $type     Message type OPTIONAL
     *
     * @return boolean
     */
    public function addBatch(array $messages, $type = self::INFO)
    {
        $result = true;

        foreach ($messages as $message) {
            $result = $result && $this->add($message, array(), null, $type);
        }

        return $result;
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = \XLite\Core\Session::getInstance()->topMessages;

        return is_array($messages) ? $messages : array();
    }

    /**
     * Get messages for AJAX response
     *
     * @return array
     */
    public function getAJAXMessages()
    {
        $messages = $this->getMessages();

        foreach ($messages as $i => $message) {
            if (!$message[static::FIELD_AJAX]) {
                unset($messages[$i]);
            }
        }

        return $messages;
    }

    /**
     * Get previous messages
     *
     * @return array
     */
    public function getPreviousMessages()
    {
        return $this->messages;
    }

    /**
     * Unload previous messages
     *
     * @return array
     */
    public function unloadPreviousMessages()
    {
        $messages = $this->getPreviousMessages();
        $this->messages = array();
        $this->clear();

        return $messages;
    }

    /**
     * Clear list
     *
     * @return void
     */
    public function clear()
    {
        if (!empty(\XLite\Core\Session::getInstance()->topMessages)) {
            unset(\XLite\Core\Session::getInstance()->topMessages);
        }
    }

    /**
     * Clear top messages list
     *
     * @return void
     */
    public function clearTopMessages()
    {
        $this->clear();
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return \XLite::isAdminZone() ? func_parse_host(\XLite::getInstance()->getShopURL(), true) : '';
    }

    /**
     * Clear only AJAX messages
     *
     * @return void
     */
    public function clearAJAX()
    {
        $messages = $this->getMessages();

        $changed = false;
        foreach ($messages as $i => $message) {
            if ($message[static::FIELD_AJAX]) {
                unset($messages[$i]);
                $changed = true;
            }
        }

        if ($changed) {
            \XLite\Core\Session::getInstance()->topMessages = $messages;
        }
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        parent::__construct();

        $this->messages = $this->getMessages();
    }
}
