<?php

namespace Storybook\Api;

use Storybook\Exception\ApiExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
final class StorybookApiCommand extends Command
{
    public function __construct(private readonly ConsoleActionInterface $action, private readonly bool $debug)
    {
        parent::__construct(\sprintf('storybook:api:%s', $this->action::getName()));
    }

    protected function configure(): void
    {
        $this->addOption('pretty', null, InputOption::VALUE_NONE, 'Output pretty JSON');

        $this->action->configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jsonFlags = $input->getOption('pretty') ? \JSON_PRETTY_PRINT : 0;

        if ($output instanceof ConsoleOutputInterface) {
            $errorOutput = $output->getErrorOutput();
        } else {
            $errorOutput = $output;
        }

        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);

        try {
            $result = $this->action->execute($input);

            $output->writeln(json_encode($result, $jsonFlags));
        } catch (\Throwable $th) {
            $res = [
                'error' => $th->getMessage()
            ];

            if ($this->debug) {
                $res += [
                    'trace' => $th->getTrace()
                ];
            }

            $errorOutput->writeln(json_encode($res, $jsonFlags));

            if ($th instanceof ApiExceptionInterface) {
                return $th->getReturnCode();
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
