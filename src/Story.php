<?php

namespace Storybook;

final class Story
{
    public function __construct(
        private readonly string $id,
        private readonly string $template,
        private readonly Args $args,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getArgs(): Args
    {
        return $this->args;
    }
}
