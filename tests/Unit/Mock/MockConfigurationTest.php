<?php

namespace Storybook\Tests\Unit\Mock;

use PHPUnit\Framework\TestCase;
use Storybook\Mock\MockConfiguration;

class MockConfigurationTest extends TestCase
{
    public function testAddMock()
    {
        $mockConfig = new MockConfiguration('Component', 'ComponentMock');

        $mockConfig->addMock('foo', 'foo', null);
        $mockConfig->addMock('bar', 'bar', null);
        $mockConfig->addMock('baz', 'baz', null);
        $mockConfig->addMock('foo', 'foo', 'story1');
        $mockConfig->addMock('bar', 'bar', 'story2');
        $mockConfig->addMock('baz', 'baz', 'story3');

        $expected = [
            'defaults' => [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ],
            'stories' => [
                'story1' => [
                    'foo' => 'foo',
                ],
                'story2' => [
                    'bar' => 'bar',
                ],
                'story3' => [
                    'baz' => 'baz',
                ],
            ],
        ];

        $this->assertEquals($expected, $mockConfig->toArray());
    }

    public function testAddSameMockMultipleTimesThrowsException()
    {
        $mockConfig = new MockConfiguration('Component', 'ComponentMock');

        $mockConfig->addMock('foo', 'foo', null);

        $this->expectException(\LogicException::class);

        $mockConfig->addMock('foo', 'foo', null);
    }
}
