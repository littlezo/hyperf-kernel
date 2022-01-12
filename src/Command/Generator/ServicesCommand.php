<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @Command
 */
#[Command]
class ServicesCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:services');
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
        // $this->getDefaultRootNamespace(), $this->getDefaultNamespace(), $this->getDefaultModule()
        $name = trim($this->input->getArgument('name'));
        $this->runGen('gen:service', $name, $this->getDefaultNamespace());
        $this->runGen('gen:factory', $name, $this->getDefaultNamespace());
        $this->runGen('gen:interface', $name, $this->getDefaultNamespace());
        // $this->genFactory($name);
        // var_dump($name);
        return $name . 'Service';
    }

    protected function getDefaultRootNamespace(): string
    {
        $root = $this->getConfig()['root_mamespace'] ?? 'App';
        return $root . '\\';
    }

    protected function getDefaultModule(): string
    {
        $module = $this->input->getOption('module');

        return $module ? $module . '\\' : '';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getDefaultRootNamespace() . $this->getDefaultModule();
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        var_dump($this->getDefaultRootNamespace(), $this->getDefaultNamespace(), $this->getDefaultModule());
        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceInterface($stub, $name);
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

    protected function runGen($command, $name, $namespace)
    {
        var_dump($command, $name, $namespace);
        $params = ['command' => $command, 'name' => $name, '--namespace' => $namespace, '--force' => true];

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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, '是否强制覆盖'],
            ['namespace', 'N', InputOption::VALUE_OPTIONAL, '命名空间 defaule(little)', null],
            ['module', 'M', InputOption::VALUE_OPTIONAL, '命名空间 defaule(null)', null],
            ['interface', 'I', InputOption::VALUE_NONE, '是否生成接口'],
            ['factory', 'F', InputOption::VALUE_NONE, '是否生成工程类'],
            ['controller', 'C', InputOption::VALUE_NONE, '是否生成控制器'],
        ];
    }
}
