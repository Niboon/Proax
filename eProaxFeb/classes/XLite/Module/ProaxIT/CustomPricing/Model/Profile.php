<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomPricing\Model;

class Profile extends \XLite\Model\Profile implements \XLite\Base\IDecorator
{
    /**
     * Customer's preferred shipping method
     *
     * @Column (type="boolean", nullable=true, options={"default"=false})
     */
    protected $preferredShipping;
    /**
     * Customer ID number
     *
     * @var string
     *
     * @Column (type="string", length=12, nullable=true)
     */
    protected $customer_id;

}