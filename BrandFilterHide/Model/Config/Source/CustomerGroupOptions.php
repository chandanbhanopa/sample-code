<?php
namespace Helm\BrandFilterHide\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;

/**
 * Create an options array for admin section
 */
class CustomerGroupOptions implements OptionSourceInterface
{
    /**
     *
     * @var Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroupCollection;

    /**
     *
     * @param CustomerGroupCollection $customerGroupCollection
     */
    public function __construct(
        CustomerGroupCollection $customerGroupCollection
    ) {
        $this->customerGroupCollection = $customerGroupCollection;
    }

    /**
     * Prepare the customer group options
     *
     * @return array $customerGroups
     */
    public function toOptionArray()
    {
        $customerGroups = $this->customerGroupCollection->toOptionArray();
        return $customerGroups;
    }
}
