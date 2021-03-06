<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Webapi\Model\Config as ModelConfig;

class DataObjectProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var ModelConfig
     */
    protected $config;

    protected function setup()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataObjectProcessor = $objectManager->getObject('Magento\Framework\Reflection\DataObjectProcessor');
        parent::setUp();
    }

    public function testDataObjectProcessor()
    {
        $objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Webapi\Test\Unit\Model\Files\TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject('Magento\Webapi\Test\Unit\Model\Files\TestDataObject');

        $expectedOutputDataArray = [
            'id' => '1',
            'address' => 'someAddress',
            'default_shipping' => 'true',
            'required_billing' => 'false',
        ];

        $testDataObjectType = 'Magento\Webapi\Test\Unit\Model\Files\TestDataInterface';
        $outputData = $this->dataObjectProcessor->buildOutputDataArray($testDataObject, $testDataObjectType);
        $this->assertEquals($expectedOutputDataArray, $outputData);
    }
}
