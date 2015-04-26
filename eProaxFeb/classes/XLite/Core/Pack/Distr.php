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

namespace XLite\Core\Pack;

/**
 * Distr
 */
class Distr extends \XLite\Core\Pack\APack
{
    /**
     * Field names in metadata
     */
    const METADATA_FIELD_VERSION_MINOR = 'VersionMinor';
    const METADATA_FIELD_VERSION_MAJOR = 'VersionMajor';

    /**
     * List of patterns which are not required in pack
     *
     * @var array
     */
    protected $exclude = array();

    /**
     * List of exception patterns
     *
     * @var array
     */
    protected $include = array();

    /**
     * Exclude pattern
     *
     * @var string
     */
    protected $excludePattern;

    /**
     * Include pattern
     *
     * @var string
     */
    protected $includePattern;

    // {{{ Public methods

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->exclude[] = 'var';
        $this->exclude[] = 'files';
        $this->exclude[] = 'images';
        $this->exclude[] = 'sql';
        $this->exclude[] = 'etc' . LC_DS . 'config.local.php';
        $this->exclude[] = 'etc' . LC_DS . 'config.personal.php';

        $this->include[] = 'var' . LC_DS . '.htaccess';
        $this->include[] = 'files' . LC_DS . '.htaccess';
        $this->include[] = 'images' . LC_DS . '.htaccess';
        $this->include[] = 'images' . LC_DS . 'spacer.gif';
        $this->include[] = 'sql' . LC_DS . 'xlite_data.yaml';
        $this->include[] = 'sql' . LC_DS . 'xlite_data_lng.yaml';
    }

    /**
     * Return pack name
     *
     * @return string
     */
    public function getName()
    {
        // It's the fix for PHAR::compress(): it's triming dots in file names
        return 'LC-Distr-v' . str_replace('.', '_', \XLite::getInstance()->getVersion());
    }

    /**
     * Return iterator to walk through directories
     *
     * @return \Iterator
     */
    public function getDirectoryIterator()
    {
        $result = new \Includes\Utils\FileFilter(LC_DIR_ROOT);
        $result = $result->getIterator();
        $this->preparePatterns();
        $result->registerCallback(array($this, 'filterCoreFiles'));

        return $result;
    }

    /**
     * Return pack metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return parent::getMetadata() + array(
            self::METADATA_FIELD_VERSION_MAJOR => \XLite::getInstance()->getMajorVersion(),
            self::METADATA_FIELD_VERSION_MINOR => \XLite::getInstance()->getMinorVersion(),
        );
    }

    /**
     * Preapre patterns
     *
     * @return void
     */
    protected function preparePatterns()
    {
        $list = array();
        foreach ($this->exclude as $pattern) {
            $list[] = preg_quote($pattern, '/');
        }

        $this->excludePattern = '/^(?:' . implode('|', $list) . ')/Ss';

        $list = array();
        foreach ($this->include as $pattern) {
            $list[] = preg_quote($pattern, '/');
        }

        $this->includePattern = '/^(?:' . implode('|', $list) . ')/Ss';

    }

    // }}}

    // {{{ Auxiliary methods

    /**
     * Callback to filter files
     *
     * @param \Includes\Utils\FileFilter\FilterIterator $iterator Directory iterator
     *
     * @return boolean
     */
    public function filterCoreFiles(\Includes\Utils\FileFilter\FilterIterator $iterator)
    {
        // Relative path in LC root directory
        $path = \Includes\Utils\FileManager::getRelativePath($iterator->getPathname(), LC_DIR_ROOT);

        return !preg_match($this->excludePattern, $path)
            || preg_match($this->includePattern, $path);
    }

    // }}}
}
