<?php

namespace Marello\Bundle\ShippingBundle\Method\Configuration\Composed;

use Marello\Bundle\ShippingBundle\Method\Configuration\AllowUnlistedShippingMethodConfigurationInterface;
use Marello\Bundle\ShippingBundle\Method\Configuration\MethodLockedShippingMethodConfigurationInterface;
use Marello\Bundle\ShippingBundle\Method\Configuration\OverriddenCostShippingMethodConfigurationInterface;
use Marello\Bundle\ShippingBundle\Method\Configuration\PreConfiguredShippingMethodConfigurationInterface;

interface ComposedShippingMethodConfigurationInterface extends
    PreConfiguredShippingMethodConfigurationInterface,
    AllowUnlistedShippingMethodConfigurationInterface,
    OverriddenCostShippingMethodConfigurationInterface,
    MethodLockedShippingMethodConfigurationInterface
{
}