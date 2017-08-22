<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OrderToPackingSlipMapperTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var EntityFieldProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFieldProvider;

    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var OrderToPackingSlipMapper
     */
    protected $orderToPackingSlipMapper;

    protected function setUp()
    {
        $this->entityFieldProvider = $this->getMockBuilder(EntityFieldProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderToPackingSlipMapper = new OrderToPackingSlipMapper(
            $this->entityFieldProvider,
            PropertyAccess::createPropertyAccessor(),
            $this->doctrineHelper
        );
    }

    public function testMap()
    {
        $warehouse = new Warehouse();
        $warehouseRepository = $this->createMock(WarehouseRepository::class);
        $warehouseRepository
            ->expects(static::once())
            ->method('getDefault')
            ->willReturn($warehouse);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->willReturn($warehouseRepository);

        $this->doctrineHelper
            ->expects(static::once())
            ->method('getEntityManagerForClass')
            ->willReturn($entityManager);

        $this->entityFieldProvider->expects($this->at(0))->method('getFields')->willReturn(
            [
                ['name' => 'id', 'identifier' => true],
                ['name' => 'salesChannel'],
                ['name' => 'customer'],
                ['name' => 'organization'],
                ['name' => 'paymentTerm'],
                ['name' => 'shippingAddress'],
                ['name' => 'billingAddress'],
                ['name' => 'items'],
            ]
        );
        $this->entityFieldProvider->expects($this->at(1))->method('getFields')->willReturn(
            [
                ['name' => 'id', 'identifier' => true],
                ['name' => 'product'],
                ['name' => 'productName'],
                ['name' => 'productSKU'],
                ['name' => 'quantity'],
            ]
        );
        $billingAddress = new MarelloAddress();
        $shippingAddress = new MarelloAddress();
        $salesChannel = new SalesChannel();
        $customer = new Customer();
        $organization = new Organization();

        $product1 = $this->getEntity(Product::class, ['id' => 1, 'weight' => 2]);
        $product2 = $this->getEntity(Product::class, ['id' => 2, 'weight' => 3]);
        $product3 = $this->getEntity(Product::class, ['id' => 3, 'weight' => 5]);

        $orderItem1 = $this->getEntity(OrderItem::class, ['id' => 1, 'product' => $product1, 'quantity' => 5]);
        $orderItem2 = $this->getEntity(OrderItem::class, ['id' => 2, 'product' => $product2, 'quantity' => 3]);
        $orderItem3 = $this->getEntity(OrderItem::class, ['id' => 3, 'product' => $product3, 'quantity' => 1]);

        $sourceEntity = $this->getEntity(Order::class, [
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'salesChannel' => $salesChannel,
            'customer' => $customer,
            'organization' => $organization,
            'items' => new ArrayCollection([$orderItem1, $orderItem2, $orderItem3])
        ]);

        $expectedItems = [
            $this->getEntity(PackingSlipItem::class, [
                'orderItem' => $orderItem1,
                'product' => $product1,
                'quantity' => $orderItem1->getQuantity(),
                'weight' => $product1->getWeight()
            ]),
            $this->getEntity(PackingSlipItem::class, [
                'orderItem' => $orderItem2,
                'product' => $product2,
                'quantity' => $orderItem2->getQuantity(),
                'weight' => $product2->getWeight()
            ]),
            $this->getEntity(PackingSlipItem::class, [
                'orderItem' => $orderItem3,
                'product' => $product3,
                'quantity' => $orderItem3->getQuantity(),
                'weight' => $product3->getWeight()
            ]),
        ];

        $expectedEntity = $this->getEntity(PackingSlip::class, [
            'order' => $sourceEntity,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'salesChannel' => $salesChannel,
            'customer' => $customer,
            'organization' => $organization,
            'items' => $expectedItems,
            'warehouse' => $warehouse
        ]);

        static::assertEquals([$expectedEntity], $result = $this->orderToPackingSlipMapper->map($sourceEntity));
    }
}
