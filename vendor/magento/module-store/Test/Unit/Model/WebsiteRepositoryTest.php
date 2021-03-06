<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config;

class WebsiteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\WebsiteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteFactoryMock;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteCollectionFactoryMock;

    /**
     * @var Config | \PHPUnit_Framework_MockObject_MockObject
     */
    private $appConfigMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->websiteFactoryMock =
            $this->getMockBuilder('Magento\Store\Model\WebsiteFactory')
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->websiteCollectionFactoryMock =
            $this->getMockBuilder('Magento\Store\Model\ResourceModel\Website\CollectionFactory')
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Store\Model\WebsiteRepository',
            [
                'factory' => $this->websiteFactoryMock,
                'websiteCollectionFactory' => $this->websiteCollectionFactoryMock
            ]
        );
        $this->appConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->initDistroList();
    }

    private function initDistroList()
    {
        $repositoryReflection = new \ReflectionClass($this->model);
        $deploymentProperty = $repositoryReflection->getProperty('appConfig');
        $deploymentProperty->setAccessible(true);
        $deploymentProperty->setValue($this->model, $this->appConfigMock);
    }

    public function testGetDefault()
    {
        $websiteMock = $this->getMockBuilder('Magento\Store\Api\Data\WebsiteInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->with('scopes', 'websites')
            ->willReturn([
                'some_code' => [
                    'code' => 'some_code',
                    'is_default' => 1
                ],
                'some_code_2' => [
                    'code' => 'some_code_2',
                    'is_default' => 0
                ]
            ]);
        $this->websiteFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($websiteMock);

        $website = $this->model->getDefault();
        $this->assertInstanceOf('Magento\Store\Api\Data\WebsiteInterface', $website);
        $this->assertEquals($websiteMock, $website);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage More than one default website is defined
     */
    public function testGetDefaultIsSeveral()
    {
        $websiteMock = $this->getMockBuilder('Magento\Store\Api\Data\WebsiteInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->with('scopes', 'websites')
            ->willReturn([
                'some_code' => [
                    'code' => 'some_code',
                    'is_default' => 1
                ],
                'some_code_2' => [
                    'code' => 'some_code_2',
                    'is_default' => 1
                ]
            ]);
        $this->websiteFactoryMock->expects($this->any())->method('create')->willReturn($websiteMock);

        $this->model->getDefault();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Default website is not defined
     */
    public function testGetDefaultIsZero()
    {
        $websiteMock = $this->getMockBuilder('Magento\Store\Api\Data\WebsiteInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->with('scopes', 'websites')
            ->willReturn([
                'some_code' => [
                    'code' => 'some_code',
                    'is_default' => 0
                ],
                'some_code_2' => [
                    'code' => 'some_code_2',
                    'is_default' => 0
                ]
            ]);
        $this->websiteFactoryMock->expects($this->any())->method('create')->willReturn($websiteMock);

        $this->model->getDefault();
    }
}
