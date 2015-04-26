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

namespace XLite\Model\Collection;

/**
 * Checkout steps list
 */
class CheckoutSteps extends \XLite\Model\Collection
{
    /**
     * current
     *
     * @var \XLite\Model\ListNode\CheckoutStep
     */
    protected $current = null;

    /**
     * actual
     *
     * @var \XLite\Model\ListNode\CheckoutStep
     */
    protected $actual = null;


    /**
     * Return current (so called "regular") step
     *
     * @return \XLite\Model\ListNode\CheckoutStep
     */
    public function getCurrentStep()
    {
        return $this->getStep(false, 'correctStep');
    }

    /**
     * Return actual ("regular" or "pseudo") checkout step
     *
     * @return \XLite\Model\ListNode\CheckoutStep
     */
    public function getActualStep()
    {
        return $this->getStep(true);
    }

    /**
     * Check if the step was corrected
     *
     * @return boolean
     */
    public function isCorrectedStep()
    {
        return $this->getCurrentStep() != $this->getActualStep();
    }


    /**
     * findLastPassedRegularStep
     *
     * @param \XLite\Model\ListNode\CheckoutStep &$step Object to prepare
     *
     * @return void
     */
    protected function findLastPassedRegularStep(\XLite\Model\ListNode\CheckoutStep &$step)
    {
        while ($step && !$step->isRegularStep()) {
            $step = $step->getPrev();
        }
    }

    /**
     * correctStep
     *
     * @param \XLite\Model\ListNode\CheckoutStep &$step Object to prepare
     *
     * @return void
     */
    protected function correctStep(\XLite\Model\ListNode\CheckoutStep &$step)
    {
        if (isset($step) && !$step->isPassed()) {
            $this->findLastPassedRegularStep($step);
        }
    }

    /**
     * getStep
     *
     * @param boolean $isActual Flag to determine step type
     * @param string  $method   Name of the callback function used to prepare step object OPTIONAL
     *
     * @return \XLite\Model\ListNode\CheckoutStep
     */
    protected function getStep($isActual, $method = null)
    {
        $name = $isActual ? 'actual' : 'current';

        if (!isset($this->$name)) {
            $this->$name = $this->findByCallbackResult('checkMode', array(\XLite\Core\Request::getInstance()->mode));

            if (isset($method)) {
                // $method is method argument. See getCurrentStep() method
                $this->$method($this->$name);
            }
        }

        return $this->$name;
    }
}
