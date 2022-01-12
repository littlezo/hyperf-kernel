<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;

/**
 * @Command
 */
#[Command]
class InterfaceCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:interface');
        $this->setDescription('Create a new interface class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/interface.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->input->getArgument('name')) . 'Interface';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Interface';
    }
}
