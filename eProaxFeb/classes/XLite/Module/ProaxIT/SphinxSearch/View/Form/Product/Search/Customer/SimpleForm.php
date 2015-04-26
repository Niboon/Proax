<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\SphinxSearch\View\Form\Product\Search\Customer;

/**
 * Simple form
 */
class SimpleForm extends  \XLite\View\Form\Product\Search\Customer\SimpleForm implements \XLite\Base\IDecorator
{
    /**
     * getDefaultParams
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        $params = parent::getDefaultParams();
        $params[\XLite\View\ItemsList\Product\Customer\Search::PARAM_INCLUDING] = \XLite\Model\Repo\Product::INCLUDING_ALL;
        return $params;
    }
}
