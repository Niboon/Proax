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
 * Abstract template patcher 
 */
abstract class Patcher extends \XLite\Base implements \XLite\Base\IPatcher
{
    /**
     * Load source as DOMDocument
     *
     * @param stirng $source HTML
     *
     * @return array
     */
    protected static function loadAsDOM($source)
    {
        $dom = new \DOMDocument();

        // DIV tag helps to avoid DOM saveHTML() method from put $source iside P tag.
        // It can be fixed in PHP 5.4 with LIBXML_HTML_NOIMPLIED parameter to loadHTML()
        $source = '<div>' . $source . '</div>';

        // Remove <info@x-cart.com> line, otherwise DOM will add <info> tags
        $source = preg_replace('/<info@x-cart.com>/', '', $source);

        return @$dom->loadHTML($source)
            ? $dom
            : null;
    }

    /**
     * Save HTML
     *
     * @param \DOMDocument $dom DOM document
     *
     * @return string
     */
    protected static function saveHTML(\DOMDocument $dom)
    {
        $xpath = new \DOMXPath($dom);

        $node = $xpath->query('//body')->item(0);

        $output = array();
        foreach ($node->childNodes as $child) {
            // Also remove DIV tag we added in patch() method (-6)
            $output[] = substr($dom->saveHTML($child), 5, -6);
        }

        $output = implode('', $output);

        $output = preg_replace('/<\/(?:widget|list)>/Ss', '', $output);
        $output = preg_replace('/(<(?:widget|list) [^>+])>/Ss', '$1 />', $output);

        return $output;
    }

}

