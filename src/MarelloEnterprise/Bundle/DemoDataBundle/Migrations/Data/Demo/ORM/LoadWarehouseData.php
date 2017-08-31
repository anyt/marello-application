<?php

namespace MarelloEnterprise\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseData as BaseWarehouseData;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;

class LoadWarehouseData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /** @var Organization $organization  */
    protected $organization;

    /** @var array $data */
    protected $data = [
        'current_default' => [
            'default'   => true,
            'type'      => 'global'
        ],
        'additional1' => [
            'name'          => 'Warehouse 13',
            'code'          => 'warehouse_13',
            'default'       => false,
            'address'       => [
                'country' => 'US',
                'street' => '2875 Hartway Street',
                'city' => 'Univille',
                'state' => 'SD',
                'postalCode' => '57078',
                'phone' => '605-857-1824',
                'company' => 'Marello'
            ],
            'type'          => 'global'
        ],
        'additional2' => [
            'name'          => 'Flagship Store',
            'code'          => 'flagship_store',
            'default'       => false,
            'address'       => [
                'country' => 'US',
                'street' => '475 5th Avenue',
                'city' => 'New York',
                'state' => 'NY',
                'postalCode' => '10017',
                'phone' => '917-536-4267',
                'company' => 'Marello'
            ],
            'type'          => 'fixed'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            BaseWarehouseData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->organization = $this->getOrganization();

        $this->loadWarehouses();
    }

    /**
     * Get organization
     * @return Organization
     */
    protected function getOrganization()
    {
        return $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }

    /**
     * load Warehouses
     */
    public function loadWarehouses()
    {
        foreach ($this->data as $warehouseKey => $data) {
            $this->createWarehouse($data);
        }
        
        $this->manager->flush();
    }

    /**
     * Create new Warehouse
     * @param array $data
     * @return Warehouse $warehouse
     */
    private function createWarehouse(array $data)
    {
        if ($data['default'] === true) {
            $warehouse = $this->manager
                ->getRepository('MarelloInventoryBundle:Warehouse')
                ->getDefault();
        } else {
            $warehouse = new Warehouse($data['name'], false);
            $warehouse->setOwner($this->organization);
            $warehouse->setCode($data['code']);

            $address = $this->createAddress($data['address']);
            $warehouse->setAddress($address);

            $this->manager->persist($warehouse);
        }

        $type = $this->getWarehouseType($data['type']);
        $warehouse->setWarehouseType($type);

        return $warehouse;
    }

    /**
     * Get Warehouse Type
     * @param $type
     * @return WarehouseType
     */
    private function getWarehouseType($type)
    {
        return $this->manager->getRepository(WarehouseType::class)->find($type);
    }

    /**
     * Create Address from dummy data
     * @param array $data
     * @return MarelloAddress
     */
    private function createAddress(array $data)
    {
        $warehouseAddress = new MarelloAddress();
        $warehouseAddress->setStreet($data['street']);
        $warehouseAddress->setPostalCode($data['postalCode']);
        $warehouseAddress->setCity($data['city']);
        /** @var Country $country */
        $country = $this->manager->getRepository('OroAddressBundle:Country')->find($data['country']);
        $warehouseAddress->setCountry($country);
        /** @var Region $region */
        $region = $this->manager
            ->getRepository('OroAddressBundle:Region')
            ->findOneBy(['combinedCode' => $data['country'] . '-' . $data['state']]);
        $warehouseAddress->setRegion($region);
        $warehouseAddress->setPhone($data['phone']);
        $warehouseAddress->setCompany($data['company']);
        $this->manager->persist($warehouseAddress);
        
        return $warehouseAddress;
    }
}
