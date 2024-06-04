<?php

declare(strict_types=1);

namespace Storybook\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\UX\TwigComponent\AnonymousComponent;
use Symfony\UX\TwigComponent\ComponentFactory;

#[AsCommand(name: 'storybook:component-file-path', hidden: true)]
class GetComponentClassPathCommand extends Command
{
    public function __construct(private readonly ComponentFactory $componentFactory)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('component', InputArgument::REQUIRED)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, default: 'json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $component = $input->getArgument('component');
        $component = $this->componentFactory->get($component);
        $refl = new \ReflectionClass($component);

        $res = [];

        if (AnonymousComponent::class !== $refl->getName()) {
            $output->writeln(json_encode($res), \JSON_PRETTY_PRINT);
        } else {
            $res += [
                'class' => $refl->getName(),
                'path' => $refl->getFileName(),
            ];

        }
        $output->writeln(json_encode($res, \JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
