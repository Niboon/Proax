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

namespace XLite\Module\XC\RESTAPI\Core\Schema;

/**
 * Complex schema
 */
class Complex extends \XLite\Module\XC\RESTAPI\Core\Schema\Native
{

    /**
     * Schema code
     */
    const CODE = 'complex';

    /**
     * Check - schema is own this request or not
     *
     * @param string $schema Schema
     *
     * @return boolean
     */
    public static function isOwn($schema)
    {
        return $schema == static::CODE;
    }

    /**
     * Check - valid or not schema
     *
     * @return boolean
     */
    public function isValid()
    {
        return parent::isValid()
            && 'get' == $this->config->shortMethod;
    }

    /**
     * Check - request is forbidden or not
     *
     * @return boolean
     */
    public function isForbid()
    {
        return parent::isForbid()
            || 'get' != $this->config->shortMethod;
    }

    /**
     * Get entity class
     *
     * @param string $path Path
     *
     * @return string
     */
    protected function getEntityClass($path)
    {
        $path = strtolower($path);
        if ('person' == $path) {
            $path = 'profile';
        }

        return parent::getEntityClass($path);
    }

    /**
     * Check - entity class is allowed or not
     *
     * @param string $class Entity class name
     *
     * @return boolean
     */
    protected function isAllowedEntityClass($class)
    {
        return parent::isAllowedEntityClass($class)
            && in_array($class, $this->getAllowedEntityClasses());
    }

    /**
     * Get allowed entity classes 
     * 
     * @return array
     */
    protected function getAllowedEntityClasses()
    {
        return array(
            'XLite\Model\Product',
            'XLite\Model\Profile',
            'XLite\Model\Order',
        );
    }

    // {{{ Convert

    /**
     * Convert model
     *
     * @param mixed   $model            Model OPTIONAL
     * @param boolean $withAssociations Convert with associations OPTIONAL
     *
     * @return mixed
     */
    protected function convertModel($model = null, $withAssociations = true)
    {
        $result = null;
        if ($model) {
            switch ($this->config->class) {
                case 'XLite\Model\Product':
                    $result = $this->convertModelProduct($model, $withAssociations);
                    break;

                case 'XLite\Model\Profile':
                    $result = $this->convertModelProfile($model, $withAssociations);
                    break;

                case 'XLite\Model\Order':
                    $result = $this->convertModelOrder($model, $withAssociations);
                    break;

                default:
            }
        }

        return $result;
    }

    /**
     * Convert model (product)
     * 
     * @param \XLite\Model\Product $model            Product
     * @param boolean              $withAssociations Convert with associations
     *  
     * @return array
     */
    protected function convertModelProduct(\XLite\Model\Product $model, $withAssociations)
    {
        $language = \XLite\Core\Config::getInstance()->General->default_language;
        $translation = $model->getSoftTranslation($language);

        $images = array();
        foreach ($model->getImages() as $image) {
            $images[] = $image->getFrontURL();
        }

        $categories = array();
        foreach ($model->getCategories() as $category) {
            $categories[] = $category->getStringPath();
        }

        $memberships = array();
        foreach ($model->getMemberships() as $membership) {
            $memberships[] = $membership->getName();
        }

        $translations = array();
        foreach ($model->getTranslations() as $translation) {
            $translations[$translation->getCode()] = array(
                'name'             => $translation->getName(),
                'description'      => $translation->getDescription(),
                'shortDescription' => $translation->getShortDescription(),
            );
        }

        return array(
            'sku'              => $model->getSku(),
            'productId'        => $model->getProductId(),
            'name'             => $translation->getName(),
            'description'      => $translation->getDescription(),
            'shortDescription' => $translation->getBriefDescription(),
            'price'            => $model->getPrice(),
            'weight'           => $model->getWeight(),
            'quantity'         => $model->getInventory()->getAmount(),
            'releaseDate'      => $model->getArrivalDate() ? date('c', $model->getArrivalDate()) : null,
            'image'            => $images,
            'URL'              => $model->getFrontURL(),
            'enabled'          => $model->getEnabled(),
            'freeShipping'     => $model->getFreeShipping(),
            'categories'       => $categories,
            'memberships'      => $memberships,
            'translations'     => $translations,
        );
    }

    /**
     * Convert model (profile)
     *
     * @param \XLite\Model\Profile $model            Profile
     * @param boolean              $withAssociations Convert with associations
     *
     * @return array
     */
    protected function convertModelProfile(\XLite\Model\Profile $model, $withAssociations)
    {
        $addresses = array();
        foreach ($model->getAddresses() as $address) {
            $addresses[] = array(
                'country'                  => $address->getCountry()->getCode(),
                'state'                    => $address->getState()->getCode() ?: $address->getState()->getState(),
                'city'                     => $address->getCity(),
                'address'                  => $address->getStreet(),
                'firstname'                => $address->getFirstname(),
                'lastname'                 => $address->getLastname(),
                'zipcode'                  => $address->getZipcode(),
                'isDefaultShippingAddress' => $address->getIsShipping(),
                'isDefaultBillingAddress'  => $address->getIsBilling(),
            );
        }

        $roles = array();
        foreach ($model->getRoles() as $role) {
            $roles[] = $role->getName();
        }

        return array(
            'profileId'  => $model->getProfileId(),
            'login'      => $model->getLogin(),
            'email'      => $model->getLogin(),
            'password'   => $model->getPassword(),
            'addresses'  => $addresses,
            'addedDate'  => $model->getAdded() ? date('c', $model->getAdded()) : null,
            'membership' => $model->getMembership() ? $model->getMembership()->getName() : '',
            'roles'      => $roles,
            'firstname'  => $model->getBillingAddress() ? $model->getBillingAddress()->getFirstname() : '',
            'lastname'   => $model->getBillingAddress() ? $model->getBillingAddress()->getLastname() : '',
        );
    }

    /**
     * Convert model (order)
     *
     * @param \XLite\Model\Order $model            Order
     * @param boolean            $withAssociations Convert with associations
     *
     * @return array
     */
    protected function convertModelOrder(\XLite\Model\Order $model, $withAssociations)
    {
        $language = \XLite\Core\Config::getInstance()->General->default_language;
        $shippingCost = $model->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_SHIPPING);

        $items = array();
        foreach ($model->getItems() as $item) {
            $translation = $item->getProduct()->getSoftTranslation($language);
            $items[] = array(
                'sku'              => $item->getSku(),
                'productId'        => $item->getProduct()->getProductId(),
                'name'             => $item->getName(),
                'description'      => $translation ? $translation->getDescription() : '',
                'shortDescription' => $translation ? $translation->getBriefDescription() : '',
                'price'            => $item->getPrice(),
                'weight'           => $item->getProduct()->getWeight(),
                'quantity'         => $item->getAmount(),
                'releaseDate'      => $item->getProduct()->getArrivalDate() ? date('c', $item->getProduct()->getArrivalDate()) : null,
                'URL'              => $item->getProduct()->getFrontURL(),
                'enabled'          => $item->getProduct()->getEnabled(),
                'freeShipping'     => $item->getProduct()->getFreeShipping(),
            );
        }

        $profile = $model->getOrigProfile() ?: $mode->getProfile();
        $baddress = $profile->getBillingAddress();
        $saddress = $profile->getShippingAddress();
        $sinfo = null;
        if ($saddress) {
            $sinfo = array(
                'country'                  => $saddress->getCountry()->getCode(),
                'state'                    => $saddress->getState()->getCode() ?: $saddress->getState()->getState(),
                'city'                     => $saddress->getCity(),
                'address'                  => $saddress->getStreet(),
                'firstname'                => $saddress->getFirstname(),
                'lastname'                 => $saddress->getLastname(),
                'zipcode'                  => $saddress->getZipcode(),
                'isDefaultShippingAddress' => $saddress->getIsShipping(),
                'isDefaultBillingAddress'  => $saddress->getIsBilling(),
            );
        }

        $paymentMethodName = $model->getPaymentMethodName();
        if (!$paymentMethodName) {
           $t = $model->getPaymentTransactions()->first();
            if ($t) {
                $paymentMethodName = $t->getPaymentMethod()
                    ? $t->getPaymentMethod()->getTitle()
                    : $t->getMethodLocalName();
            }
        }

        return array(
            'orderId'        => $model->getOrderId(),
            'orderNumber'    => $model->getOrderNumber(),
            'total'          => $model->getTotal(),
            'subtotal'       => $model->getTotal() - $shippingCost,
            'taxAmount'      => $model->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_TAX),
            'shippingCost'   => $shippingCost,
            'paymentFee'     => 0,
            'discountValue'  => $model->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_DISCOUNT),
            'currency'       => $model->getCurrency()->getCode(),
            'items'          => $items,
            'shippingStatus' => $model->getShippingStatusCode(),
            'paymentStatus'  => $model->getPaymentStatusCode(),
            'orderDate'      => date('c', $model->getDate()),
            'updateDate'     => date('c', $model->getLastRenewDate()),
            'trackingNumber' => $model->getTrackingNumbers()->first() ? $model->getTrackingNumbers()->first()->getValue() : '',
            'customerNotes'  => $model->getNotes(),
            'adminNotes'     => $model->getAdminNotes(),
            'coupon'         => '',
            'customerInfo'   => array(
                'profileId'  => $profile->getProfileId(),
                'login'      => $profile->getLogin(),
                'email'      => $profile->getLogin(),
                'password'   => $profile->getPassword(),
                'addedDate'  => date('c', $profile->getDate()),
                'membership' => $profile->getMembership() ? $profile->getMembership()->getName() : '',
                'firstname'  => $profile->getBillingAddress() ? $profile->getBillingAddress()->getFirstname() : '',
                'lastname'   => $profile->getBillingAddress() ? $profile->getBillingAddress()->getLastname() : '',
            ),
            'billingInfo'    => array(
                'country'                  => $baddress->getCountry()->getCode(),
                'state'                    => $baddress->getState()->getCode() ?: $baddress->getState()->getState(),
                'city'                     => $baddress->getCity(),
                'address'                  => $baddress->getStreet(),
                'firstname'                => $baddress->getFirstname(),
                'lastname'                 => $baddress->getLastname(),
                'zipcode'                  => $baddress->getZipcode(),
                'isDefaultShippingAddress' => $baddress->getIsShipping(),
                'isDefaultBillingAddress'  => $baddress->getIsBilling(),
            ),
            'shippingInfo'   => $sinfo,
            'paymentMethod'  => $paymentMethodName,
            'shippingMethod' => $model->getShippingMethodName(),
        );
    }

    // }}}

    // {{{ Input

    /**
     * Prepare input
     *
     * @param array  $data   Data
     *
     * @return array
     */
    protected function prepareInput(array $data)
    {
        list($checked, $data) = parent::prepareInput($data);

        if ($checked) {
            switch ($this->config->class) {
                case 'XLite\Model\Product':
                    list($checked, $data) = $this->prepareInputProduct($data);
                    break;

                case 'XLite\Model\Profile':
                    list($checked, $data) = $this->prepareInputProfile($data);
                    break;

                case 'XLite\Model\Order':
                    list($checked, $data) = $this->prepareInputOrder($data);
                    break;

                default:
            }
        }

        return array($checked, $data);
    }

    /**
     * Prepare input (product)
     * 
     * @param array $data Data
     *  
     * @return array
     */
    protected function prepareInputProduct(array $data)
    {
        list($checked, $result) = $this->prepareScalarFields($data, $this->getProductFields());

        // Inventory
        if ($checked && isset($result['amount'])) {
            $result['inventory'] = array(
                'amount' => $result['amount'],
            );
            unset($result['amount']);
        }

        // Images
        if ($checked && !empty($data['image']) && is_array($data['image'])) {
            $result['images'] = array();
            foreach ($data['image'] as $url) {
                if (!empty($url)) {
                    $result['images'][] = array(
                        'loadURL' => $url,
                    );

                } else {
                    $checked = false;
                }
            }
        }

        // Categories
        if ($checked && !empty($data['categories']) && is_array($data['categories'])) {
            $orderby = 10;
            $result['categoryProducts'] = array();
            foreach ($data['categories'] as $path) {
                if (is_string($path) && !empty($path)) {
                    $category = \XLite\Core\Database::getRepo('XLite\Model\Category')->findOneByPath(explode('/', $path));
                    if ($category) {
                        $result['categoryProducts'][] = array(
                            'category' => array(
                                'category_id' => $category->getCategoryId(),
                            ),
                            'orderby' => $orderby,
                        );
                        $orderby += 10;

                    } else {
                        $checked = false;
                    }
                }
            }
        }

        // Memberships
        if ($checked && !empty($data['memberships']) && is_array($data['memberships'])) {
            $result['memberships'] = array();
            foreach ($data['memberships'] as $membership) {
                if (is_string($membership) && !empty($membership)) {
                    $membership = \XLite\Core\Database::getRepo('XLite\Model\Membership')->findOneByName($membership);
                    if ($membership) {
                        $result['memberships'][] = array(
                            'membership_id' => $membership->getMembershipId(),
                        );

                    } else {
                        $checked = false;
                    }
                }
            }
        }

        // Translations
        if ($checked && !empty($data['translations']) && is_array($data['translations'])) {
            $result['translations'] = array();
            foreach ($data['translations'] as $code => $translation) {
                if (!empty($translation) && is_array($translation)) {
                    $result['translations'][] = array('code' => $code)
                        + $translation;

                } else {
                    $checked = false;
                }
            }
        }

        return array($checked, $result);
    }

    /**
     * Get product fields 
     * 
     * @return array
     */
    protected function getProductFields()
    {
        return array(
            array(
                'source'      => 'sku',
                'destination' => 'sku',
                'required'    => true,
            ),
            array(
                'source'      => 'name',
                'destination' => 'name',
            ),
            array(
                'source'      => 'description',
                'destination' => 'description',
            ),
            array(
                'source'      => 'shortDescription',
                'destination' => 'brief_description',
            ),
            array(
                'source'      => 'price',
                'destination' => 'price',
            ),
            array(
                'source'      => 'weight',
                'destination' => 'weight',
            ),
            array(
                'source'      => 'releaseDate',
                'destination' => 'arrivalDate',
            ),
            array(
                'source'      => 'enabled',
                'destination' => 'enabled',
            ),
            array(
                'source'      => 'URL',
                'destination' => 'cleanURL',
            ),
            array(
                'source'      => 'freeShipping',
                'destination' => 'free_shipping',
            ),
            array(
                'source'      => 'quantity',
                'destination' => 'amount',
            ),
        );
    }

    /**
     * Prepare input (profile)
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function prepareInputProfile(array $data)
    {
    }

    /**
     * Prepare input (order)
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function prepareInputOrder(array $data)
    {
    }

    /**
     * Prepare scalar fields 
     * 
     * @param array $data   Data
     * @param array $fields Fields definition
     *  
     * @return array
     */
    protected function prepareScalarFields(array $data, array $fields)
    {
        $checked = true;
        $result = array();

        foreach ($fields as $field) {
            if (!empty($field['required']) && (!isset($data[$field['source']]) || 0 == strlen($data[$field['source']]))) {
                $checked = false;
                break;
            }

            if (isset($data[$field['source']])) {
                $result[$field['destination']] = $data[$field['source']];
            }
        }

        return array($checked, $result);
    }

    // }}}

    // {{{ Common routines

    /**
     * Load data
     *
     * @param \XLite\Model\AEntity $entity Entity
     * @param array                $data   Data
     *
     * @return void
     */
    protected function loadData(\XLite\Model\AEntity $entity, array $data)
    {
        if ($entity instanceOf \XLite\Model\Product) {
            $this->preloadProductData($entity, $data);

        } elseif ($entity instanceOf \XLite\Model\Profile) {
            $this->preloadProfileData($entity, $data);

        } elseif ($entity instanceOf \XLite\Model\Order) {
            $this->preloadOrderData($entity, $data);

        }

        parent::loadData($entity, $data);
    }

    /**
     * Peload product data 
     * 
     * @param \XLite\Model\AEntity $entity Product
     * @param array                $data   Data
     *  
     * @return void
     */
    protected function preloadProductData(\XLite\Model\AEntity $entity, array $data)
    {
    }

    /**
     * Peload profile data
     *
     * @param \XLite\Model\AEntity $entity Profile
     * @param array                $data   Data
     *
     * @return void
     */
    protected function preloadProfileData(\XLite\Model\AEntity $entity, array $data)
    {
    }

    /**
     * Peload order data
     *
     * @param \XLite\Model\AEntity $entity Order
     * @param array                $data   Data
     *
     * @return void
     */
    protected function preloadOrderData(\XLite\Model\AEntity $entity, array $data)
    {
    }

    // }}}
}
