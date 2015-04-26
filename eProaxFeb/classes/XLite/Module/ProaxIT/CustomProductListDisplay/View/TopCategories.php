<?php
/**
 * Created by PhpStorm.
 * User: proaxit
 * Date: 2015-03-13
 * Time: 11:33 AM
 */
namespace XLite\Module\ProaxIT\CustomProductListDisplay\View;


/**
 * Class TopCategories
 * Remove Categories sidebar from the Main Page
 *
 * @package XLite\Module\ProaxIT\CustomProductListDisplay\View
 */
class TopCategories extends \XLite\View\TopCategories implements \XLite\Base\IDecorator
{
    /**
     * @inherit
     */
    protected function isVisible()
    {
        return $this->checkTarget() &&
            $this->checkMode() &&
            $this->checkACL()  &&
            $this->getTarget() != 'main'  &&
            $this->getTarget() != 'home'  &&
            false;
    }
}