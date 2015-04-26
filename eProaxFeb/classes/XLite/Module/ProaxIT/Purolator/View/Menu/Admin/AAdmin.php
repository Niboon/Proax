<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator\View\Menu\Admin;

/**
 * Admin menu extension
 */
abstract class AAdmin extends \XLite\View\Menu\Admin\AAdmin implements \XLite\Base\IDecorator
{
    /**
     * Returns the list of related targets
     *
     * @param string $target Target name
     *
     * @return array
     */
    public function getRelatedTargets($target)
    {
        $targets = parent::getRelatedTargets($target);

        if ('shipping_methods' == $target) {
            $targets[] = 'purolator';
        }

        return $targets;
    }
}
