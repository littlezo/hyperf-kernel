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
class FactoryCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:factory');
        $this->setDescription('Create a new interface class');
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceService($stub, $name);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceService(&$stub, $name)
    {
        $service = str_replace('Factory', 'Service', str_replace($this->getNamespace($name) . '\\', '', $name));
        $serviceClass = str_replace('Factory', 'Service', $name);
        $stub = str_replace('%SERVICECLASS%', $serviceClass, $stub);
        return str_replace('%SERVICE%', $service, $stub);
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/factory.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->input->getArgument('name')) . 'Factory';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Factory';
    }
}
