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

namespace XLite\Module\XC\RESTAPI\Model;

/**
 * Abstract entity
 */
abstract class AEntity extends \XLite\Model\AEntity implements \XLite\Base\IDecorator
{

    // {{{ REST API :: get

    /**
     * Build model data for REST API
     *
     * @param boolean $withAssociations Convert with associations OPTIONAL
     *
     * @return mixed
     */
    public function buildDataForREST($withAssociations = true)
    {
        $data = $this->buildFieldsforREST();

        if ($withAssociations) {
            $data += $this->buildAssociationsforREST();
        }

        return $data;
    }

    /**
     * Build plain fields
     *
     * @return array
     */
    protected function buildFieldsforREST()
    {
        $data = array();

        foreach ($this->getModelFieldsForREST() as $name => $field) {
            $data[$name] = $this->getterPropertyForREST($name, $field);
        }

        return $data;
    }

    /**
     * Build associations fields
     *
     * @return array
     */
    protected function buildAssociationsforREST()
    {
        $data = array();

        foreach ($this->getModelAssociationsForREST() as $name => $field) {
            $data[$name] = $this->getterAssociationForREST($name, $field);
        }

        return $data;
    }

    /**
     * Get model fields list for REST API
     *
     * @return array
     */
    protected function getModelFieldsForREST()
    {
        return $this->getRepository()->getPublicClassMetadata()->fieldMappings;
    }

    /**
     * Get model associations list for REST API
     *
     * @return array
     */
    public function getModelAssociationsForREST()
    {
        return $this->getRepository()->getPublicClassMetadata()->associationMappings;
    }

    /**
     * Plain property getter for REST API
     *
     * @param string $name  Field name
     * @param array  $field Field metadata
     *
     * @return string
     */
    protected function getterPropertyForREST($name, array $field)
    {
        $value = $this->getterProperty($name);
        switch ($field['type']) {
            case 'integer':
                $value = intval($value);
                break;

            case 'decimal':
                $value = doubleval($value);
                break;

            default:
        }

        return $value;
    }

    /**
     * Association getter for REST API
     *
     * @param string $name  Association name
     * @param array  $field Association metadata
     *
     * @return string
     */
    protected function getterAssociationForREST($name, array $field)
    {
        $value = null;

        $association = $this->getterProperty($name);
        if ($association) {
            if ($association instanceOf \XLite\Model\AEntity) {
                $value = $association->buildDataForREST(false);

            } else {
                $value = array();
                foreach ($association as $submodel) {
                    $value[] = $submodel->buildDataForREST(false);
                }
            }
        }

        return $value;
    }

    // }}}

}
