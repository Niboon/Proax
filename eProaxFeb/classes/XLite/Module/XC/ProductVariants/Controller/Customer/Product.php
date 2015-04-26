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

namespace XLite\Module\XC\ProductVariants\Controller\Customer;

/**
 * Product
 */
class Product extends \XLite\Controller\Customer\Product implements \XLite\Base\IDecorator
{

    /**
     * Get variant images 
     * 
     * @return void
     */
    protected function doActionGetVariantImages()
    {
        $data = null;

        if ($this->getProduct()->mustHaveVariants()) {
            $ids = array();
            $attributeValues = trim(\XLite\Core\Request::getInstance()->{\XLite\View\Product\Details\Customer\Widget::PARAM_ATTRIBUTE_VALUES}, ',');

            if ($attributeValues) {
                $attributeValues = explode(',', $attributeValues);
                foreach ($attributeValues as $v) {
                    $v = explode('_', $v);
                    $ids[$v[0]] = $v[1];
                }
            }

            $productVariant = $this->getProduct()->getVariant(
                $this->getProduct()->prepareAttributeValues($ids)
            );

            if ($productVariant && $productVariant->getImage()) {
                $data = $this->assembleVariantImageData($productVariant->getImage());
            }
        }

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
    }

    /**
     * Assemble variant image data 
     * 
     * @param \XLite\Module\XC\ProductVariants\Model\Image\ProductVariant\Image $image Image
     *  
     * @return array
     */
    protected function assembleVariantImageData(\XLite\Model\Base\Image $image)
    {
        $result = array(
            'full' => array(
                $image->getWidth(),
                $image->getHeight(),
                $image->getURL(),
                $image->getAlt(),
            ),
        );

        foreach ($this->getImageSizes() as $name => $sizes) {
            $result[$name] = $image->getResizedURL($sizes[0], $sizes[1]);
            $result[$name][3] = $image->getAlt();
        }

        return $result;
    }

    /**
     * Get image sizes 
     * 
     * @return array
     */
    protected function getImageSizes()
    {
        return array(
            'gallery' => array(
                60,
                60,
            ),
            'main'    => array(
                \XLite\Core\Config::getInstance()->General->product_page_image_width,
                \XLite\Core\Config::getInstance()->General->product_page_image_height,
            ),
        );
    }

}
