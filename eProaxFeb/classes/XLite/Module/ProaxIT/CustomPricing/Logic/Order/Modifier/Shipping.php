<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomPricing\Logic\Order\Modifier;

/**
 * Custom Shipping modifier for preferred shipping
 */
class Shipping extends \XLite\Logic\Order\Modifier\Shipping implements \XLite\Base\IDecorator
{
    public $preferredFlag = null;
    const FIXED_PREF_SHIP_POSITION = 1337;

    /**
     * Calculate shipping rates
     * Remove PreferredShipping if $preferredFlag is false
     *
     * @return array(\XLite\Model\Shipping\Rate)
     */
    protected function calculateRates()
    {
        $rates = parent::calculateRates();
        if (!$this->defaultToPreferred()) {
            foreach ($rates as $key => $rate) {
                if ($rate->getMethod()) {
                    if ($rate->getMethod()->getPosition() == self::FIXED_PREF_SHIP_POSITION) {
                        unset($rates[$key]);
                    }
                }
            }
        }

        return $rates;
    }

    /**
     * Shipping rates sorting callback
     *
     * @param \XLite\Model\Shipping\Rate $a First shipping rate
     * @param \XLite\Model\Shipping\Rate $b Second shipping rate
     *
     * @return integer
     */
    protected function compareRates(\XLite\Model\Shipping\Rate $a, \XLite\Model\Shipping\Rate $b)
    {
        $result = parent::compareRates($a,$b);

        if ($this->defaultToPreferred()) {
            $this->processPreferredMethod($a,$b,$result);
        }

        return $result;
    }

    /**
     * @param \XLite\Model\Shipping\Rate $a First shipping rate
     * @param \XLite\Model\Shipping\Rate $b Second shipping rate
     * @param int $result Passed-By-Reference to modify if PreferredShipping is true
     */
    protected function processPreferredMethod($a, $b, &$result)
    {
        $methodA = $a->getMethod();
        $methodB = $b->getMethod();
        if (isset($methodA) && isset($methodB)) {
            $positionA = $methodA->getPosition();
            $positionB = $methodB->getPosition();

            // Determine result to return for defaulting to PreferredShipping if position matches PreferredShipping
            if ($positionA == self::FIXED_PREF_SHIP_POSITION) {
                $result = -1;
            } elseif ($positionB == self::FIXED_PREF_SHIP_POSITION) {
                $result = 1;
            }
        }
    }

    /**
     * @return bool preferredFlag If preferred shipment option should be defaulted to
     */
    public function defaultToPreferred()
    {
        if (!isset($this->preferredFlag)) {
            $profile = \XLite\Core\Auth::getInstance()->getProfile();
            if ($profile && $profile->getCustomerId() && $profile->getPreferredShipping()) {
                $this->preferredFlag = true;
            } else {
                $this->preferredFlag = false;
            }
        }
        return $this->preferredFlag;
    }
}
