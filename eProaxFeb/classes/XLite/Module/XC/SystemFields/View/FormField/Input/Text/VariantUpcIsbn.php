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

namespace XLite\Module\XC\SystemFields\View\FormField\Input\Text;

/**
 * Variant UPC/ISBN
 *
 * @LC_Dependencies("XC\ProductVariants")
 */
class VariantUpcIsbn extends \XLite\Module\XC\SystemFields\View\FormField\Input\Text\UpcIsbn
    implements \XLite\Base\IDecorator
{
    /**
     * Check field validity
     *
     * @return boolean
     */
    protected function checkFieldValidity()
    {
        $result = parent::checkFieldValidity();

        if ($result) {
            if (!\XLite\Core\Converter::isEmptyString($this->getValue())) {
                $upcIsbn = \XLite\Core\Database::getRepo('XLite\Module\XC\ProductVariants\Model\ProductVariant')
                    ->findOneByUpcIsbn($this->getValue());

                $result = !(
                    $upcIsbn
                    && $upcIsbn->getProductId() !== $this->getProductId()
                    && !\XLite\Core\Database::getRepo('XLite\Model\Product')->findOneByUpcIsbn($this->getValue())
                );

                if (!$result) {
                    $this->errorMessage = static::t('UPC/ISBN must be unique');
                }
            }
        }

        return $result;
    }
}
