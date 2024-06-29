<?php

namespace Storybook\Tests\Integration\Mock;

use Storybook\Tests\Fixtures\Component\LiveComponent;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

class LiveActionMockTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    public function testLiveActionMocks()
    {
        /** @var KernelBrowser $client */
        $client = self::getContainer()->get('test.live_component_client');

        $testComponent = $this->createLiveComponent(
            name: LiveComponent::class,
            data: [
                'prop' => 'foo',
            ],
            client: $client
        );

        $this->assertStringContainsString('Prop: foo', $testComponent->render());

        $testComponent->call('mockedAction', ['value' => 'bar']);

        $rendered = $testComponent->render();
        $this->assertStringContainsString('Prop: foobar', $rendered);
        $this->assertStringContainsString('Message: mock called', $rendered);

        $testComponent->call('notMockedAction', ['value' => 'baz']);

        $rendered = $testComponent->render();
        $this->assertStringContainsString('Prop: foobarbaz', $rendered);
        $this->assertStringContainsString('Message: not mock called', $rendered);
    }
}
