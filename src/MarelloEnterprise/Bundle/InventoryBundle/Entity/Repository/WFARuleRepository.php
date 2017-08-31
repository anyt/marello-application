<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityProBundle\ORM\Walker\AclHelper;

class WFARuleRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }
    
    /**
     * @return array
     */
    public function getUsedStrategies()
    {
        $qb = $this
            ->createQueryBuilder('wfa')
            ->distinct(true)
            ->select('wfa.strategy');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }
}
