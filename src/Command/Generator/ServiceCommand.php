<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @Command
 */
#[Command]
class ServiceCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:service');
        $this->setDescription('Create a new service class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/service.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->input->getArgument('name'));
        // $this->genInterface($name);
        // $this->genFactory($name);
        // var_dump($name);
        return $name . 'Service';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Service';
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
        $stub = $this->replaceInterface($stub, $name);
        // var_dump($stub);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceInterface(&$stub, $name)
    {
        $interface = str_replace('Service', 'Interface', str_replace($this->getNamespace($name) . '\\', '', $name));
        $interfaceClass = str_replace('Service', 'Interface', $name);
        // var_dump($interface, $interfaceClass);
        $stub = str_replace('%INTERFACECLASS%', $interfaceClass, $stub);
        return str_replace('%INTERFACE%', $interface, $stub);
    }

    protected function genInterface($name)
    {
        $command = 'gen:interface';

        $params = ['command' => $command, 'name' => $name, '--force' => true];

        // 可以根据自己的需求, 选择使用的 input/output
        $input = new ArrayInput($params);
        $output = new NullOutput();

        /** @var \Psr\Container\ContainerInterface $container */
        $container = \Hyperf\Utils\ApplicationContext::getContainer();

        /** @var \Symfony\Component\Console\Application $application */
        $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
        $application->setAutoExit(false);

        // 这种方式: 不会暴露出命令执行中的异常, 不会阻止程序返回
        // $exitCode = $application->run($input, $output);
        $exitCode = $application->find($command)->run($input, $output);
        // 第二种方式: 会暴露异常, 需要自己捕捉和处理运行中的异常, 否则会阻止程序的返回
    }

    protected function genFactory($name)
    {
        $command = 'gen:factory';

        $params = ['command' => $command, 'name' => $name, '--force' => true];

        // 可以根据自己的需求, 选择使用的 input/output
        $input = new ArrayInput($params);
        $output = new NullOutput();

        /** @var \Psr\Container\ContainerInterface $container */
        $container = \Hyperf\Utils\ApplicationContext::getContainer();

        /** @var \Symfony\Component\Console\Application $application */
        $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
        $application->setAutoExit(false);

        // 这种方式: 不会暴露出命令执行中的异常, 不会阻止程序返回
        // $exitCode = $application->run($input, $output);
        $exitCode = $application->find($command)->run($input, $output);
        // 第二种方式: 会暴露异常, 需要自己捕捉和处理运行中的异常, 否则会阻止程序的返回
    }
}
