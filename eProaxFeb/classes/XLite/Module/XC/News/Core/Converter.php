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

namespace XLite\Module\XC\News\Core;

/**
 * Miscelaneous convertion routines
 */
class Converter extends \XLite\Core\Converter implements \XLite\Base\IDecorator
{
    /**
     * Get clean URL book
     *
     * @param string $url  Main part of a clean URL
     * @param string $last First part before the "url" OPTIONAL
     * @param string $rest Part before the "url" and "last" OPTIONAL
     * @param string $ext  Extension OPTIONAL
     *
     * @return array
     */
    protected static function getCleanURLBook($url, $last = '', $rest = '', $ext = '')
    {
        $list = parent::getCleanURLBook($url, $last, $rest, $ext);

        $list['news_message'] = '\XLite\Module\XC\News\Model\NewsMessage';

        return $list;
    }

    /**
     * Compose clean URL
     *
     * @param string $target Page identifier OPTIONAL
     * @param string $action Action to perform OPTIONAL
     * @param array  $params Additional params OPTIONAL
     *
     * @return string
     */
    public static function buildCleanURL($target = '', $action = '', array $params = array())
    {
        $result = null;
        $urlParams = array();

        if ('news_message' === $target && !empty($params['id'])) {
            $page = \XLite\Core\Database::getRepo('\XLite\Module\XC\News\Model\NewsMessage')->find($params['id']);

            if (isset($page) && $page->getCleanURL()) {
                $urlParams[] = $page->getCleanURL() . '.html';

                unset($params['id']);
            }
        }

        if (!empty($urlParams)) {
			static::buildCleanURLHook($target, $action, $params, $urlParams);

            unset($params['target']);

            $result  = \Includes\Utils\ConfigParser::getOptions(array('host_details', 'web_dir_wo_slash'));
            $result .= '/' . implode('/', array_reverse($urlParams));

            if (!empty($params)) {
                $result .= '?' . http_build_query($params);
            }
        }

        return $result ?: parent::buildCleanURL($target, $action, $params);
    }

}
