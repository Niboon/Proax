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
 * Iconv wrapper
 */
class Iconv extends \XLite\Base\Singleton
{
    /**
     * Charsets 
     * 
     * @var array
     */
    protected $charsets;

    /**
     * Check - iconv wrapper is valid
     * 
     * @return boolean
     */
    public function isValid()
    {
        return function_exists('iconv')
            && $this->getCharsets();
    }


    /**
     * Get charsets 
     * 
     * @return array
     */
    public function getCharsets()
    {
        if (!isset($this->charsets)) {
            $this->charsets = array();
            if (function_exists('iconv')) {
                $exec = func_find_executable('iconv');
                if ($exec) {
                    $output = array();
                    exec($exec . ' -l', $output);

                    if (is_array($output)) {

                        $output = implode(' ', $output);

                        preg_match_all('/\S+/Ssm', $output, $match);

                        if (!empty($match[0])) {
                            $output = $match[0];
                            sort($output);

                            foreach ($output as $v) {
                                $v = rtrim($v, '/');
                                $this->charsets[$v] = str_replace('_', ' ', $v);
                            }
                        }
                    }
                }
            }
        }

        return $this->charsets;
    }

    /**
     * Convert charset
     * 
     * @param string $from From charset
     * @param string $to   To charset
     * @param string $text Text
     *  
     * @return string
     */
    public function convert($from, $to, $text)
    {
        return function_exists('iconv') ? iconv($from, $to, $text) : $text;
    }

    /**
     * Convert charset
     *
     * @param string $from       From charset
     * @param string $to         To charset
     * @param string $path       File path
     * @param string $outputPath Output file path OPTIONAL
     *
     * @return boolean
     */
    public function convertFile($from, $to, $path, $outputPath = null)
    {
        $result = false;

        $outputPath = $outputPath ?: $path;

        $exec = func_find_executable('iconv');
        if ($exec) {
            exec(
                $exec
                . ' -s'
                . ' --from-code=' . $from
                . ' --to-code=' . $to
                . ' --output=' . escapeshellarg($outputPath)
                . ' < ' . escapeshellarg($path)
            );
            $result = true;
        }

        return $result;
    }

}
