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

namespace XLite\Module\CDev\ContactUs\Core;

/**
 * Mailer
 */
abstract class Mailer extends \XLite\Core\Mailer implements \XLite\Base\IDecorator
{
    /**
     * New mail type
     */
    const TYPE_CONTACT_US = 'ContactUs';

    /**
     * `From` storage
     *
     * @var string
     */
    protected static $fromStorage = null;

    /**
     * Make some specific preparations for "Custom Headers" for SiteAdmin email type
     *
     * @param array  $customHeaders "Custom Headers" field value
     *
     * @return array new "Custom Headers" field value
     */
    protected static function prepareCustomHeadersContactUs($customHeaders)
    {
        $customHeaders[] = 'Reply-To: ' . static::$fromStorage;

        return $customHeaders;
    }

    /**
     * Send contact us message
     *
     * @param array  $data  Data
     * @param string $email Email
     *
     * @return string | null
     */
    public static function sendContactUsMessage(array $data, $email)
    {
        static::$fromStorage = $data['email'];
        $data['message'] = htmlspecialchars($data['message']);

        static::register('data', $data);

        static::compose(
            static::TYPE_CONTACT_US,
            static::getSiteAdministratorMail(),
            $email,
            'modules/CDev/ContactUs/message',
            array(),
            true,
            \XLite::ADMIN_INTERFACE
        );

        return static::getMailer()->getLastError();
    }
}
