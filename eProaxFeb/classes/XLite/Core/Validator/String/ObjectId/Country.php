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

namespace XLite\Core\Validator\String\ObjectId;

/**
 * Country code
 */
class Country extends \XLite\Core\Validator\String
{
    /**
     * Only-enabled country validation flag
     *
     * @var boolean
     */
    protected $onlyEnabled = false;

    /**
     * Constructor
     *
     * @param boolean $nonEmpty    Non-empty flag OPTIONAL
     * @param boolean $onlyEnabled Only enabled flag OPTIONAL
     *
     * @return void
     */
    public function __construct($nonEmpty = false, $onlyEnabled = false)
    {
        parent::__construct($nonEmpty);

        $this->onlyEnabled = $onlyEnabled;
    }

    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    public function validate($data)
    {
        parent::validate($data);

        if (0 < strlen($data)) {
            $country = $this->sanitize($data);
            if (!$country) {
                throw $this->throwError('Not a country code');

            } else {
                if ($this->onlyEnabled && !$country->getEnabled()) {
                    throw $this->throwError('Country is not enabled');
                }
            }
        }
    }

    /**
     * Sanitize
     *
     * @param mixed $data Daa
     *
     * @return mixed
     */
    public function sanitize($data)
    {
        return 0 < strlen($data)
            ? \XLite\Core\Database::getRepo('XLite\Model\Country')->find($data)
            : null;
    }

}
