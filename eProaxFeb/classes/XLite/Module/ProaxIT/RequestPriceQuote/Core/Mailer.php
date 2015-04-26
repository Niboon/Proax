<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote\Core;

/**
 * Mailer
 */
abstract class Mailer extends \XLite\Core\Mailer implements \XLite\Base\IDecorator
{
    /**
     * New mail type
     */
    const TYPE_REQUEST_PRICE_QUOTE = 'RequestPriceQuote';

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
    protected static function prepareCustomHeadersRequestPriceQuote($customHeaders)
    {
        $customHeaders[] = 'Reply-To: ' . static::$fromStorage;

        return $customHeaders;
    }

    /**
     * Send Request Price Quote message
     *
     * @param array  $data  Data
     * @param string $email Email
     *
     * @return string | null
     */
    public static function sendRequestPriceQuoteMessage(array $data, $email)
    {
        static::setMailInterface(\XLite::MAIL_INTERFACE);
        static::$fromStorage = $data['email'];
        $data['message'] = htmlspecialchars($data['message']);

        static::register('data', $data);

        static::compose(
            static::TYPE_REQUEST_PRICE_QUOTE,
            static::getSiteAdministratorMail(),
            $email,
            'modules/ProaxIT/RequestPriceQuote/message'
        );

        return static::getMailer()->getLastError();
    }
}
