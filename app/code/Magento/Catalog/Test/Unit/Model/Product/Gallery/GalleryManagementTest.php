<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

class GalleryManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\GalleryManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryEntryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\AttributeValue
     */
    protected $attributeValueMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->contentValidatorMock = $this->getMock('\Magento\Catalog\Model\Product\Gallery\ContentValidator');
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [
                'setStoreId',
                'getData',
                'getStoreId',
                'getSku',
                'getCustomAttribute',
                'getMediaGalleryEntries',
                'setMediaGalleryEntries',
            ],
            [],
            '',
            false
        );
        $this->mediaGalleryEntryMock =
            $this->getMock('Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $this->model = new \Magento\Catalog\Model\Product\Gallery\GalleryManagement(
            $this->storeManagerMock,
            $this->productRepositoryMock,
            $this->contentValidatorMock
        );
        $this->attributeValueMock = $this->getMockBuilder('\Magento\Framework\Api\AttributeValue')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no store with provided ID.
     */
    public function testCreateWithNoStoreException()
    {
        $this->storeManagerMock->expects($this->once())->method('getStore')
            ->willThrowException(new \Exception());
        $this->model->create('sku', $this->mediaGalleryEntryMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content is not valid.
     */
    public function testCreateWithInvalidImageException()
    {
        $entryContentMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface'
        );
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $storeId = 0;

        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId);
        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(false);

        $this->model->create("sku", $this->mediaGalleryEntryMock, $storeId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot save product.
     */
    public function testCreateWithCannotSaveException()
    {
        $productSku = 'mediaProduct';
        $entryContentMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface'
        );
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $storeId = 0;
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId);
        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(true);

        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->create($productSku, $this->mediaGalleryEntryMock, $storeId);
    }

    public function testCreate()
    {
        $productSku = 'mediaProduct';
        $entryContentMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface'
        );
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $storeId = 0;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock)
            ->willReturn($this->productMock);

        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId);
        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(true);

        $newEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $newEntryMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->productMock->expects($this->at(2))->method('getMediaGalleryEntries')
            ->willReturn([$newEntryMock]);
        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$this->mediaGalleryEntryMock]);

        $this->assertEquals(42, $this->model->create($productSku, $this->mediaGalleryEntryMock, $storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no store with provided ID.
     */
    public function testUpdateWithNonExistingStore()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $storeId = 0;
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)
            ->willThrowException(new \Exception());
        $this->model->update($productSku, $entryMock, $storeId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testUpdateWithNonExistingImage()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $storeId = 0;
        $entryId = 42;
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(43);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->model->update($productSku, $entryMock, $storeId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot save product.
     */
    public function testUpdateWithCannotSaveException()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $storeId = 0;
        $entryId = 42;
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->update($productSku, $entryMock, $storeId);
    }

    public function testUpdate()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $storeId = 0;
        $entryId = 42;
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);

        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->willReturn([$entryMock]);
        $this->productMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->model->update($productSku, $entryMock, $storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testRemoveWithNonExistingImage()
    {
        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(43);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->model->remove($productSku, $entryId);
    }

    public function testRemove()
    {
        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(42);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([]);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->model->remove($productSku, $entryId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Such product doesn't exist
     */
    public function testGetWithNonExistingProduct()
    {
        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willThrowException(new \Exception());
        $this->model->get($productSku, $imageId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionText Such image doesn't exist
     */
    public function testGetWithNonExistingImage()
    {
        $productSku = 'testProduct';
        $imageId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(44);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->model->get($productSku, $imageId);
    }

    public function testGet()
    {
        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(42);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->assertEquals($existingEntryMock, $this->model->get($productSku, $imageId));
    }

    public function testGetList()
    {
        $productSku = 'testProductSku';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $entryMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface');
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$entryMock]);
        $this->assertEquals([$entryMock], $this->model->getList($productSku));
    }
}
