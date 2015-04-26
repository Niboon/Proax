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

namespace XLite\Logic;

/**
 * Price 
 */
class Price extends \XLite\Logic\ALogic
{
    /**
     * Modifiers 
     * 
     * @var array
     */
    protected $modifiers;

    /**
     * Apply price modifiers
     * 
     * @param \XLite\Model\AEntity $model     Model
     * @param string               $method    Model's getter
     * @param array                $behaviors Behaviors OPTIONAL
     * @param string               $purpose   Purpose OPTIONAL
     *  
     * @return float
     */
    public function apply(\XLite\Model\AEntity $model, $method, array $behaviors = array(), $purpose = 'net')
    {
        $property = lcfirst(substr($method, 3));
        $value = $model->$method();

        $modifiers = $this->prepareModifiers($this->getModifiers(), $behaviors, $purpose);
        foreach ($modifiers as $modifier) {
            $value = $modifier->apply($value, $model, $property, $behaviors, $purpose);
        }

        return $value;
    }

    /**
     * Get modifiers 
     * 
     * @return array
     */
    protected function getModifiers()
    {
        if (!isset($this->modifiers)) {
            $this->modifiers = $this->defineModifiers();
        }

        return $this->modifiers;
    }

    /**
     * Define modifiers 
     * 
     * @return array
     */
    protected function defineModifiers()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\MoneyModificator')->findActive();
    }

    /**
     * Prepare modifiers 
     * 
     * @param array  $modifiers Modifiers list
     * @param array  $behaviors Behaviors
     * @param string $purpose   Purpose
     *  
     * @return array
     */
    protected function prepareModifiers(array $modifiers, array $behaviors, $purpose)
    {
        foreach($modifiers as $i => $modifier) {
            if (
                ($modifier->getPurpose() && $modifier->getPurpose() != $purpose)
                || ($modifier->getBehavior() && !in_array($modifier->getBehavior(), $behaviors))
            ) {
                unset($modifiers[$i]);
            }
        }

        return $modifiers;
    }
}

