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

namespace XLite\Logic\Import\Step\Base;

/**
 * Abstract data import step
 */
abstract class DataStep extends \XLite\Logic\Import\Step\AStep
{
    /**
     * Last import processor (cache)
     *
     * @var   \XLite\Logic\Import\Processor\AProcessor
     */
    protected $lastProcessor;

    /**
     * Constructor
     *
     * @param \XLite\Logic\Import\Importer $importer Importer
     * @param integer                      $index    Step index
     *
     * @return void
     */
    public function __construct(\XLite\Logic\Import\Importer $importer, $index)
    {
        parent::__construct($importer, $index);

        $this->importer->getOptions()->rowsCount = $this->count();
    }

    /**
     * Get current processor
     *
     * @return \XLite\Logic\Import\Processor\AProcessor
     */
    protected function getProcessor()
    {
        if ($this->getOptions()->position != $this->lastPosition || !isset($this->lastProcessor)) {
            $i = $this->getOptions()->position;
            foreach ($this->importer->getProcessors() as $processor) {
                $this->lastProcessor = $processor;
                $count = $processor->count();

                if (0 >= $count) {
                    continue;
                }

                if ($i < $count) {
                    $processor->seek(max($i, 0));

                    if (!$processor->isEof()) {
                        break;
                    }
                }

                $i -= $count;
            }

            $this->lastPosition = $this->getOptions()->position;
        }

        return $this->lastProcessor;
    }

    /**
     * Check valid state of step
     *
     * @return boolean
     */
    public function isValid()
    {
        return parent::isValid()
            && $this->getProcessor()
            && $this->getProcessor()->isValid();
    }

    // {{{ Countable

    /**
     * \Counable::count
     *
     * @return integer
     */
    public function count()
    {
        $result = 0;
        foreach ($this->importer->getProcessors() as $processor) {
            $result += $processor->count();
        }

        return $result;
    }

    // }}}
}
