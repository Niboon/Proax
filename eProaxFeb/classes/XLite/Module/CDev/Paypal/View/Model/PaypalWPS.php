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

namespace XLite\Module\CDev\Paypal\View\Model;

/**
 * PaypalWPS
 */
class PaypalWPS extends \XLite\Module\CDev\Paypal\View\Model\ASettings
{
    /**
     * Schema of the "Your account settings" section
     *
     * @var array
     */
    protected $schemaAccount = array(
        'account' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'PayPal ID / Email',
            self::SCHEMA_HELP     => 'Enter the email address associated with your PayPal account.',
            self::SCHEMA_REQUIRED => true,
        ),
    );

    /**
     * Schema of the "Additional settings" section
     *
     * @var array
     */
    protected $schemaAdditional = array(
        'description' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Purchase description',
            self::SCHEMA_HELP     => 'Enter description of the purchase that will be displayed on PayPal payment page.',
            self::SCHEMA_REQUIRED => true,
        ),
        'mode' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Select\TestLiveMode',
            self::SCHEMA_LABEL    => 'Test/Live mode',
            self::SCHEMA_REQUIRED => false,
        ),
        'prefix' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Order id prefix',
            self::SCHEMA_HELP     => 'You can define an order id prefix, which would precede each order number in your shop, to make it unique',
            self::SCHEMA_REQUIRED => false,
        ),
    );
}
