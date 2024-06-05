<?php

namespace Storybook\Api;

use Storybook\Exception\ApiErrorException;
use Storybook\Exception\ApiInvalidInputException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
class GetBundleConfigAction extends AbstractAction
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public static function getName(): string
    {
        return 'bundle-config';
    }

    public function __invoke(...$args): mixed
    {
        ['name' => $name] = $args;

        if (null === $name) {
            throw new ApiInvalidInputException('Missing name argument');
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'debug:config',
            'name' => $name,
            '--format' => 'json',
            '--resolve-env' => true,
        ]);

        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);

        try {
            $retCode = $application->doRun($input, $output);
        } catch (\Exception $e) {
            throw new ApiErrorException($e->getMessage());
        }

        $content = $output->fetch();

        if (Command::SUCCESS !== $retCode) {
            throw new ApiErrorException($content);
        }

        return \json_decode($content, associative: true, flags: \JSON_THROW_ON_ERROR);
    }

    public function handleRequest(Request $request): mixed
    {
        $name = $request->query->get('name');

        return $this(name: $name);
    }

    public function execute(InputInterface $input): mixed
    {
        $name = $input->getArgument('name');

        return $this(name: $name);
    }

    public function configure(Command $command): void
    {
        $command->addArgument('name', InputArgument::REQUIRED);
    }
}
