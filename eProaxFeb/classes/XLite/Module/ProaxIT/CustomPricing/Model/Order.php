<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomPricing\Model;

abstract class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    const PAYMENT_ID_TO_HIDE_FROM_GUESTS = 1;

    public function getPaymentMethods()
    {
        if (0 < $this->getOpenTotal()) {

            $list = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                ->findAllActive();

            foreach ($list as $i => $method) {
//                if (!$method->isEnabled() || !$method->getProcessor()->isApplicable($this, $method)) {
                if (
                    !$method->isEnabled() ||
                    !$method->getProcessor()->isApplicable($this, $method) ||
                    ($method->getMethodId() == self::PAYMENT_ID_TO_HIDE_FROM_GUESTS &&
                        !( \XLite\Core\Auth::getInstance()->getProfile() &&  \XLite\Core\Auth::getInstance()->getProfile()->getCustomerId())
                    )
                ) {
                    unset($list[$i]);
                }
            }

        } else {
            $list = array();
        }

        return $list;
    }
}