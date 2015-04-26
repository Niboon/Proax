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
 * Miscellaneous conversion routines
 */
class Converter extends \XLite\Base\Singleton
{
    /**
     * Sizes
     */
    const GIGABYTE = 1073741824;
    const MEGABYTE = 1048576;
    const KILOBYTE = 1024;

    /**
     * Cache driver
     *
     * @var \XLite\Core\Cache\Registry
     */
    protected static $cacheDriver;

    /**
     * Cache controllers
     *
     * @var array
     */
    protected static $cacheControllers;

    /**
     * Method name translation records
     *
     * @var array
     */
    protected static $to = array(
        'Q', 'W', 'E', 'R', 'T',
        'Y', 'U', 'I', 'O', 'P',
        'A', 'S', 'D', 'F', 'G',
        'H', 'J', 'K', 'L', 'Z',
        'X', 'C', 'V', 'B', 'N',
        'M',
    );

    /**
     * Method name translation patterns
     *
     * @var array
     */
    protected static $from = array(
        '_q', '_w', '_e', '_r', '_t',
        '_y', '_u', '_i', '_o', '_p',
        '_a', '_s', '_d', '_f', '_g',
        '_h', '_j', '_k', '_l', '_z',
        '_x', '_c', '_v', '_b', '_n',
        '_m',
    );

    /**
     * Translite map
     *
     * @var array
     */
    protected static $translitMap = array(
        '!'     => '161',
        '"'     => '1066,1098,8220,8221,8222',
        "'"     => '1068,1100,8217,8218',
        '\'\''  => '147,148',
        '(R)'   => '174',
        '(TM)'  => '153,8482',
        '(c)'   => '169',
        '+-'    => '177',
        '-'     => '47,92,172,173,8211', # Replace spaces/slashes by the $subst_symbol('-' by default)
        '.'     => '183',
        '...'   => '8230',
        '0/00'  => '8240',
        '<'     => '8249',
        '<<'    => '171',
        '>'     => '8250',
        '>>'    => '187',
        '?'     => '191',
        'A'     => '192,193,194,195,196,197,256,258,260,1040,7840,7842,7844,7846,7848,7850,7852,7854,7856,7858,7860,7862',
        'AE'    => '198',
        'B'     => '1041,1042',
        'C'     => '199,262,264,266,268,1062',
        'CH'    => '1063',
        'Cx'    => '264',
        'D'     => '208,270,272,1044',
        'D%'    => '1026',
        'DS'    => '1029',
        'DZ'    => '1039',
        'E'     => '200,201,202,203,274,276,278,280,282,1045,7864,7866,7868,7870,7872,7874,7876,7878',
        'EUR'   => '128,8364',
        'F'     => '1060',
        'G'     => '284,286,288,290,1043',
        'G%'    => '1027',
        'G3'    => '1168',
        'Gx'    => '284',
        'H'     => '292,294,1061',
        'Hx'    => '292',
        'I'     => '204,205,206,207,296,298,300,302,304,1048,7880,7882',
        'IE'    => '1028',
        'II'    => '1030',
        'IO'    => '1025',
        'J'     => '308,1049',
        'J%'    => '1032',
        'Jx'    => '308',
        'K'     => '310,1050',
        'KJ'    => '1036',
        'L'     => '163,313,315,317,319,321,1051',
        'LJ'    => '1033',
        'M'     => '1052',
        'N'     => '209,323,325,327,330,1053',
        'NJ'    => '1034',
        'No.'   => '8470',
        'O'     => '164,210,211,212,213,214,216,332,334,336,416,467,1054,7884,7886,7888,7890,7892,7894,7896,7898,7900,7902,7904,7906',
        'OE'    => '140,338',
        'P'     => '222,1055',
        'R'     => ',340,342,344,1056',
        'S'     => '138,346,348,350,352,1057',
        'SCH'   => '1065',
        'SH'    => '1064',
        'Sx'    => '348',
        'T'     => '354,356,358,1058',
        'Ts'    => '1035',
        'U'     => '217,218,219,220,360,362,364,366,368,370,431,1059,7908,7910,7912,7914,7916,7918,7920',
        'Ux'    => '364',
        'V'     => '1042',
        'V%'    => '1038',
        'W'     => '372',
        'Y'     => '159,221,374,376,1067,7922,7924,7926,7928',
        'YA'    => '1071',
        'YI'    => '1031',
        'YU'    => '1070',
        'Z'     => '142,377,379,381,1047',
        'ZH'    => '1046',
        '`'     => '8216',
        '`E'    => '1069',
        '`e'    => '1101',
        'a'     => '224,225,226,227,228,229,257,259,261,1072,7841,7843,7845,7847,7849,7851,7853,7855,7857,7859,7861,7863',
        'ae'    => '230',
        'b'     => '1073,1074',
        'c'     => '162,231,263,265,267,269,1094',
        'ch'    => '1095',
        'cx'    => '265',
        'd'     => '271,273,1076',
        'd%'    => '1106',
        'ds'    => '1109',
        'dz'    => '1119',
        'e'     => '232,233,234,235,275,277,279,281,283,1077,7865,7867,7869,7871,7873,7875,7877,7879',
        'f'     => '131,402,1092',
        'g'     => '285,287,289,291,1075',
        'g%'    => '1107',
        'g3'    => '1169',
        'gx'    => '285',
        'h'     => '293,295,1093',
        'hx'    => '293',
        'i'     => '236,237,238,239,297,299,301,303,305,1080,7881,7883',
        'ie'    => '1108',
        'ii'    => '1110',
        'io'    => '1105',
        'j'     => '309,1081',
        'j%'    => '1112',
        'jx'    => '309',
        'k'     => '311,312,1082',
        'kj'    => '1116',
        'l'     => '314,316,318,320,322,1083',
        'lj'    => '1113',
        'm'     => '1084',
        'mu'    => '181',
        'n'     => '241,324,326,328,329,331,1085',
        'nj'    => '1114',
        'o'     => '186,176,242,243,244,245,246,248,333,335,337,417,449,1086,7885,7887,7889,7891,7893,7895,7897,7899,7901,7903,7905,7907',
        'oe'    => '156,339',
        'p'     => '167,182,254,1087',
        'r'     => '341,343,345,1088',
        's'     => '154,347,349,351,353,1089',
        'sch'   => '1097',
        'sh'    => '1096',
        'ss'    => '223',
        'sx'    => '349',
        't'     => '355,357,359,1090',
        'ts'    => '1115',
        'u'     => '249,250,251,252,361,363,365,367,369,371,432,1091,7909,7911,7913,7915,7917,7919,7921',
        'ux'    => '365',
        'v'     => '1074',
        'v%'    => '1118',
        'w'     => '373',
        'y'     => '253,255,375,1099,7923,7925,7927,7929',
        'ya'    => '1103',
        'yen'   => '165',
        'yi'    => '1111',
        'yu'    => '1102',
        'z'     => '158,378,380,382,1079',
        'zh'    => '1078',
        '|'     => '166',
        '~'     => '8212',
    );

    /**
     * Flag to avoid multiple setlocale() calls
     *
     * @var boolean
     */
    protected static $isLocaleSet = false;

    /**
     * Charsets table
     *
     * @var array
     */
    protected static $charsetsTable = array(
        'ru' => 'WINDOWS-1251',
    );

    /**
     * Convert a string like "test_foo_bar" into the camel case (like "TestFooBar")
     *
     * @param string $string String to convert
     *
     * @return string
     */
    public static function convertToCamelCase($string)
    {
        return ucfirst(str_replace(self::$from, self::$to, strval($string)));
    }

    /**
     * Convert a string like "testFooBar" into the underline style (like "test_foo_bar")
     *
     * @param string $string String to convert
     *
     * @return string
     */
    public static function convertFromCamelCase($string)
    {
        return str_replace(self::$to, self::$from, lcfirst(strval($string)));
    }

    /*
     *  Convert a string like "testFooBar" to translit
     *
     * @param string $string String to convert
     *
     * @return string
     */
    public static function convertToTranslit($string)
    {
        $tr = array();

        foreach (static::$translitMap as $letter => $set) {
            $letters = explode(',', $set);
            foreach ($letters as $v) {
                if ($v < 256) {
                    $tr[chr($v)] = $letter;
                }
                $tr['&#' . $v . ';'] = $letter;
            }

        }

        for ($i = 0; $i < 256; $i++) {
            if (empty($tr['&#' . $i . ';'])) {
                $tr['&#' . $i . ';'] = chr($i);
            }
        }

        if (function_exists('mb_encode_numericentity')) {
            $string = mb_encode_numericentity($string, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
        }

        return strtr($string, $tr);
    }

    /**
     * Prepare method name
     *
     * @param string $string Underline-style string
     *
     * @return string
     */
    public static function prepareMethodName($string)
    {
        return str_replace(self::$from, self::$to, strval($string));
    }

    /**
     * Compose controller class name using target
     *
     * @param string $target Current target
     *
     * @return string
     */
    public static function getControllerClass($target)
    {
        if (\XLite\Core\Request::getInstance()->isCLI()) {
            $zone = 'Console';

        } elseif (\XLite::isAdminZone()) {
            $zone = 'Admin';

        } else {
            $zone = 'Customer';
        }

        $target = static::convertToCamelCase($target);

        // Initialize cache
        if (!isset(static::$cacheControllers)) {
            static::$cacheControllers = static::getSystemCacheDriver()->fetch('controllers');
            if (!is_array(static::$cacheControllers)) {
                static::$cacheControllers = array();
            }
        }

        // Check cache
        $class = isset(static::$cacheControllers[$zone . '.' . $target])
            ? static::$cacheControllers[$zone . '.' . $target]
            : null;

        if (!$class) {
            // Detect
            $prefix = 'Controller\\' . $zone . '\\' . static::convertToCamelCase($target);
            $class = 'XLite\\' . $prefix;

            // If non core controller
            if (!\XLite\Core\Operator::isClassExists($class)) {
                $class = null;
                $base = LC_DIR_CACHE_CLASSES;
                $path = $base . 'XLite' . LC_DS
                    . 'Module' . LC_DS
                    . '*' . LC_DS
                    . '*' . LC_DS
                    . 'Controller' . LC_DS
                    . $zone . LC_DS
                    . $target . '.php';

                $list = glob($path);
                if ($list) {
                    $length = strlen(LC_DIR_CACHE_CLASSES);
                    foreach ($list as $path) {
                        $preclass = str_replace(LC_DS, '\\', substr($path, $length, -4));
                        $reflection = new \ReflectionClass($preclass);
                        // Check - decorator or not
                        if (!$reflection->isAbstract()) {
                            $class = $preclass;
                            break;
                        }
                    }
                }
            }

            if ($class) {
                static::$cacheControllers[$zone . '.' . $target] = $class;
                static::getSystemCacheDriver()->save('controllers', static::$cacheControllers);
            }
        }

        return $class;
    }

    /**
     * Get cache driver
     *
     * @return \XLite\Core\Cache\Registry
     */
    public static function getSystemCacheDriver()
    {
        if (!isset(static::$cacheDriver)) {
            static::$cacheDriver = new \XLite\Core\Cache\Registry('system');
        }

        return static::$cacheDriver;
    }

    // {{{ URL routines

    /**
     * Compose URL from target, action and additional params
     *
     * @param string  $target        Page identifier OPTIONAL
     * @param string  $action        Action to perform OPTIONAL
     * @param array   $params        Additional params OPTIONAL
     * @param string  $interface     Interface script OPTIONAL
     * @param boolean $forceCleanURL Force flag - use Clean URL OPTIONAL
     * @param boolean $forceCuFlag   Force cu flag ?? OPTIONAL
     *
     * @return string
     */
    public static function buildURL($target = '', $action = '', array $params = array(), $interface = null, $forceCleanURL = false, $forceCuFlag = null)
    {
        $result = null;
        $cuFlag = !is_null($forceCuFlag) ? $forceCuFlag : (LC_USE_CLEAN_URLS && (!\XLite::isAdminZone() || $forceCleanURL));

        if ($cuFlag) {
            $result = static::buildCleanURL($target, $action, $params);
        }

        if (!isset($result)) {
            if (!isset($interface) && !$cuFlag) {
                $interface = \XLite::getInstance()->getScript();
            }

            $result = \Includes\Utils\Converter::buildURL($target, $action, $params, $interface);
            if ($cuFlag && !$result) {
                $result = \XLite::getInstance()->getShopURL(
                    $result,
                    null,
                    array()
                );
            }
        }

        return $result;
    }

    /**
     * Compose full URL from target, action and additional params
     *
     * @param string $target    Page identifier OPTIONAL
     * @param string $action    Action to perform OPTIONAL
     * @param array  $params    Additional params OPTIONAL
     * @param string $interface Interface script OPTIONAL
     *
     * @return string
     */
    public static function buildFullURL($target = '', $action = '', array $params = array(), $interface = null)
    {
        return \XLite::getInstance()->getShopURL(static::buildURL($target, $action, $params, $interface));
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
        return \XLite\Core\Database::getRepo('XLite\Model\CleanURL')->buildURL($target, $params);
    }

    /**
     * Make specified URL a valid for W3C-validation
     *
     * @param string $url URL
     *
     * @return string
     */
    public static function makeURLValid($url)
    {
        if (false === strstr('&amp;', $url)) {
            $url = str_replace('&', '&amp;', $url);
        }

        return $url;
    }

    /**
     * Parse clean URL (<rest>/<last>/<url>(?:\.<ext="htm">(?:l)))
     *
     * @param string $url  Main part of a clean URL
     * @param string $last First part before the "url" OPTIONAL
     * @param string $rest Part before the "url" and "last" OPTIONAL
     * @param string $ext  Extension OPTIONAL
     *
     * @return array
     */
    public static function parseCleanUrl($url, $last = '', $rest = '', $ext = '')
    {
        return \XLite\Core\Database::getRepo('XLite\Model\CleanURL')->parseURL($url, $last, $rest, $ext);
    }

    /**
     * Return pattern to check clean URLs
     *
     * @param boolean $getAllowedPattern Get allowed chars pattern if true,
     *                                   otherwise get unallowed chars pattern OPTIONAL
     *
     * @return string
     */
    public static function getCleanURLAllowedCharsPattern($getAllowedPattern = true)
    {
        return $getAllowedPattern ? '[\w_\-]+' : '[^\w_\-]';
    }

    // }}}

    // {{{ Others

    /**
     * Convert to one-dimensional array
     *
     * @param array  $data    Array to flat
     * @param string $currKey Parameter for recursive calls OPTIONAL
     *
     * @return array
     */
    public static function convertTreeToFlatArray(array $data, $currKey = '')
    {
        $result = array();

        foreach ($data as $key => $value) {
            $key = $currKey . (empty($currKey) ? $key : '[' . $key . ']');
            $result += is_array($value) ? self::convertTreeToFlatArray($value, $key) : array($key => $value);
        }

        return $result;
    }

    /**
     * Generate random token (32 chars)
     *
     * @return string
     */
    public static function generateRandomToken()
    {
        return md5(microtime(true) + rand(0, 1000000));
    }

    /**
     * Check - is GDlib enabled or not
     *
     * @return boolean
     */
    public static function isGDEnabled()
    {
        return function_exists('imagecreatefromjpeg')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagealphablending')
            && function_exists('imagesavealpha')
            && function_exists('imagecopyresampled');
    }

    /**
     * Check if specified string is URL or not
     *
     * @param string $url URL
     *
     * @return boolean
     */
    public static function isURL($url)
    {
        static $pattern = '(?:([a-z][a-z0-9\*\-\.]*):\/\/(?:(?:(?:[\w\.\-\+!$&\'\(\)*\+,;=]|%[0-9a-f]{2})+:)*(?:[\w\.\-\+%!$&\'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:(?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?](?:[\w#!:\.\?\+=&@!$\'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?)';

        return is_string($url) && 0 < preg_match('/^' . $pattern . '$/Ss', $url);
    }

    /**
     * Check for empty string
     *
     * @param string $string String to check
     *
     * @return boolean
     */
    public static function isEmptyString($string)
    {
        return '' === $string || false === $string;
    }

    /**
     * Return class name without backslashes
     *
     * @param \XLite\Base $obj Object to get class name from
     *
     * @return string
     */
    public static function getPlainClassName(\XLite\Base $obj)
    {
        return str_replace('\\', '', get_class($obj));
    }

    /**
     * Convert value from one to other weight units
     *
     * @param float  $value   Weight value
     * @param string $srcUnit Source weight unit
     * @param string $dstUnit Destination weight unit
     *
     * @return float
     */
    public static function convertWeightUnits($value, $srcUnit, $dstUnit)
    {
        $unitsInGrams = array(
            'lbs' => 453.59,
            'oz'  => 28.35,
            'kg'  => 1000,
            'g'   => 1,
        );

        $multiplier = $unitsInGrams[$srcUnit] / $unitsInGrams[$dstUnit];

        return $value * $multiplier;
    }

    /**
     * Get server timstamp with considering server time zone
     *
     * @param \DateTimeZone $timeZone Server time zone OPTIONAL
     *
     * @return integer
     */
    public static function time($timeZone = null)
    {
        if (!empty($timeZone) && is_string($timeZone)) {
            // If timeZone is string create DateTimeZone object
            $timeZone = new \DateTimeZone($timeZone);
        }

        $time = ($timeZone instanceof \DateTimeZone ? new \DateTime('now', $timeZone) : new \DateTime());

        return $time->getTimestamp();
    }

    /**
     * Format time
     *
     * @param integer $base                  UNIX time stamp OPTIONAL
     * @param string  $format                Format string OPTIONAL
     * @param boolean $convertToUserTimeZone True if time value should be converted according to the time zone OPTIONAL
     *
     * @return string
     */
    public static function formatTime($base = null, $format = null, $convertToUserTimeZone = true)
    {
        if (!$format) {
            $config = \XLite\Core\Config::getInstance();
            $format = $config->Units->date_format . ', ' . $config->Units->time_format;
        }

        if ($convertToUserTimeZone) {
            $base = \XLite\Core\Converter::convertTimeToUser($base);
        }

        return static::getStrftime($format, $base);
    }

    /**
     * Format date
     *
     * @param integer $base                  UNIX time stamp OPTIONAL
     * @param string  $format                Format string OPTIONAL
     * @param boolean $convertToUserTimeZone True if time value should be converted according to the time zone OPTIONAL
     *
     * @return string
     */
    public static function formatDate($base = null, $format = null, $convertToUserTimeZone = true)
    {
        if (!$format) {
            $format = \XLite\Core\Config::getInstance()->Units->date_format;
        }

        if ($convertToUserTimeZone) {
            $base = \XLite\Core\Converter::convertTimeToUser($base);
        }

        return static::getStrftime($format, $base);
    }

    /**
     * Format day time
     *
     * @param integer $base                  UNIX time stamp OPTIONAL
     * @param string  $format                Format string OPTIONAL
     * @param boolean $convertToUserTimeZone True if time value should be converted according to the time zone OPTIONAL
     *
     * @return string
     */
    public static function formatDayTime($base = null, $format = null, $convertToUserTimeZone = true)
    {
        if (!$format) {
            $format = \XLite\Core\Config::getInstance()->Units->time_format;
        }

        if ($convertToUserTimeZone) {
            $base = \XLite\Core\Converter::convertTimeToUser($base);
        }

        return static::getStrftime($format, $base);
    }

    /**
     * Get strftime() with specified format and timestamp value
     *
     * @param string  $format Format string
     * @param integer $base   UNIX time stamp OPTIONAL
     *
     * @return string
     */
    protected static function getStrftime($format, $base = null)
    {
        static::setLocaleToUTF8();

        $win = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';

        if ($win) {
            $format = str_replace('%e', '%#d', $format);
        }

        $date = isset($base) ? strftime($format, $base) : strftime($format);

        if ($win) {
            $locale = setlocale(LC_TIME, 0);

            if (preg_match('/(([^_]+)_?([^.]*))\.?(.*)?/', $locale, $match)
                && preg_match('/^.+_.+\.(\d+)$/', $match[0], $match)
                && \XLite\Core\Iconv::getInstance()->isValid()
            ) {
                $date = \XLite\Core\Iconv::getInstance()->convert('WINDOWS-' . $match[1], 'UTF-8', $date) ?: $date;

            } elseif (!empty(static::$charsetsTable[$locale])) {
                $date = \XLite\Core\Iconv::getInstance()->convert(static::$charsetsTable[$locale], 'UTF-8', $date) ?: $date;
            }
        }

        return $date;
    }

    /**
     * Attempt to set locale to UTF-8
     *
     * @return void
     */
    protected static function setLocaleToUTF8()
    {
        if (!self::$isLocaleSet
            && preg_match('/(([^_]+)_?([^.]*))\.?(.*)?/', setlocale(LC_TIME, 0), $match)
            && !preg_match('/utf\-?8/i', $match[4])
        ) {
            $lng = \XLite\Core\Session::getInstance()->getLanguage();
            $localeCode = $lng->getCode() . '_' . strtoupper($lng->getCode());
            $localeCode3 = $lng->getCode3();

            setlocale(
                LC_TIME,
                $localeCode . '.UTF-8',
                $localeCode,
                $lng->getCode(),
                $localeCode3,
                $match[0]
            );

            self::$isLocaleSet = true;
        }
    }

    // }}}

    // {{{ File size

    /**
     * Prepare human-readable output for file size
     *
     * @param integer $size Size in bytes
     *
     * @return string
     */
    public static function formatFileSize($size)
    {
        list($size, $suffix) = \Includes\Utils\Converter::formatFileSize($size);

        return $size . ' ' . ($suffix ? static::t($suffix) : '');
    }

    /**
     * Convert short size (2M, 8K) to human readable
     *
     * @param string $size Shortsize
     *
     * @return string
     */
    public static function convertShortSizeToHumanReadable($size)
    {
        $size = static::convertShortSize($size);

        if (static::GIGABYTE < $size) {
            $label = 'X GB';
            $size = round($size / static::GIGABYTE, 3);

        } elseif (static::MEGABYTE < $size) {
            $label = 'X MB';
            $size = round($size / static::MEGABYTE, 3);

        } elseif (static::KILOBYTE < $size) {
            $label = 'X kB';
            $size = round($size / static::KILOBYTE, 3);

        } else {
            $label = 'X bytes';
        }

        return \XLite\Core\Translation::lbl($label, array('value' => $size));
    }

    /**
     * Convert short size (2M, 8K) to normal size (in bytes)
     *
     * @param string $size Short size
     *
     * @return integer
     */
    public static function convertShortSize($size)
    {
        if (preg_match('/^(\d+)([a-z])$/Sis', $size, $match)) {
            $size = intval($match[1]);
            switch ($match[2]) {
                case 'G':
                    $size *= 1073741824;
                    break;

                case 'M':
                    $size *= 1048576;
                    break;

                case 'K':
                    $size *= 1024;
                    break;

                default:
            }

        } else {
            $size = intval($size);
        }

        return $size;
    }

    // }}}

    // {{{ Time

    /**
     * Convert user time to server time
     *
     * @param integer $time User time
     *
     * @return integer
     */
    public static function convertTimeToServer($time)
    {
        $server = new \DateTime();
        $server = $server->getTimezone()->getOffset($server);

        $user = new \DateTime();
        $timeZone = \XLite\Core\Config::getInstance()->Units->time_zone ?: $user->getTimezone()->getName();
        $user->setTimezone(new \DateTimeZone($timeZone));
        $user = $user->getTimezone()->getOffset($user);

        $offset = $server - $user;

        return $time + $offset;
    }

    /**
     * Convert server time to user time
     *
     * @param integer $time Server time
     *
     * @return integer
     */
    public static function convertTimeToUser($time = null)
    {
        if (!isset($time)) {
            $time = \XLite\Core\Converter::time();
        }

        $server = new \DateTime();
        $server = $server->getTimezone()->getOffset($server);

        $user = new \DateTime();
        $timeZone = \XLite\Core\Config::getInstance()->Units->time_zone ?: $user->getTimezone()->getName();
        $user->setTimezone(new \DateTimezone($timeZone));
        $user = $user->getTimezone()->getOffset($user);

        $offset = $server - $user;

        return $time - $offset;
    }

    // }}}
}
