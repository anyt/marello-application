<?php

namespace MarelloEnterprise\Bundle\PackingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloEnterprisePackingBundle extends Bundle
{
    public function getParent()
    {
        return 'MarelloPackingBundle';
    }
}
