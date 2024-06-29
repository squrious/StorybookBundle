<?php

namespace Storybook\Tests\Unit\Mock;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Storybook\Mock\ComponentMock;
use Storybook\Mock\ComponentMockFactory;

class ComponentMockFactoryTest extends TestCase
{
    public function testCreateComponentMock()
    {
        $locator = $this->createMock(ContainerInterface::class);
        $mockFactory = new ComponentMockFactory($locator);
        $mockFactory->addMockConfiguration('Component', [], []);

        $locator->expects($this->once())->method('get')->with('Component')->willReturn(new \stdClass());
        $proxy = $mockFactory->create('Component', 'story');
        $this->assertInstanceOf(ComponentMock::class, $proxy);
    }

    /**
     * @dataProvider getConfigurations
     */
    public function testComponentHasMock(array $configurations, string $componentClass, bool $expected)
    {
        $mockFactory = new ComponentMockFactory($this->createMock(ContainerInterface::class));

        foreach ($configurations as $configuration) {
            $mockFactory->addMockConfiguration($configuration['componentClass'], $configuration['mockConfig'] ?? []);
        }

        $this->assertEquals($expected, $mockFactory->componentHasMock($componentClass));
    }

    public static function getConfigurations(): iterable
    {
        yield 'No configuration' => [
            [],
            'Component',
            false,
        ];

        yield 'Component class matches configured mocks' => [
            [
                [
                    'componentClass' => 'Component',
                    'mockConfig' => [],
                ],
            ],
            'Component',
            true,
        ];

        yield 'Component class does not match configured mocks' => [
            [
                [
                    'componentClass' => 'Component',
                    'mockConfig' => [],
                ],
            ],
            'OtherComponent',
            false,
        ];
    }

    public function testMockingTheSameComponentMultipleTimesThrowsException()
    {
        $mockFactory = new ComponentMockFactory($this->createMock(ContainerInterface::class));
        $mockFactory->addMockConfiguration('Component', []);

        $this->expectException(\LogicException::class);
        $mockFactory->addMockConfiguration('Component', []);
    }

    public function testMissingMockProviderThrowsLogicException()
    {
        $locator = $this->createMock(ContainerInterface::class);
        $mockFactory = new ComponentMockFactory($locator);
        $mockFactory->addMockConfiguration('Component', []);

        $locator->expects($this->once())->method('get')->with('Component')->willThrowException($this->createMock(NotFoundExceptionInterface::class));
        $this->expectException(\LogicException::class);
        $mockFactory->create('Component', 'story');
    }
}
