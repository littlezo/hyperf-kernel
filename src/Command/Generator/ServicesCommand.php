<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
#[Command]
class ServicesCommand extends HyperfCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected $root = 'little';

    protected $module = '';

    protected $name = '';

    public function __construct()
    {
        parent::__construct('gen:services');
        $this->setDescription('Create a new service class');
    }

    public function configure()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption(...$option);
        }
    }

    public function handle()
    {
        if ($root = $this->input->getOption('namespace')) {
            $this->root = trim($root);
        }
        var_dump($root);
        if ($module = $this->input->getOption('module')) {
            $this->module = Str::studly(trim($module));
        }
        if ($name = trim($this->input->getArgument('name'))) {
            $this->name = Str::studly($name);
        }
        // '--force'=>
        $args = [
            'name' => $this->name,
            '--namespace' => $this->getDefaultNamespace(),
            '--module' => $this->module,
            '--force' => $this->input->getOption('force') ?? false,
            '--interface' => $this->input->getOption('interface') ?? false,
            '--factory' => $this->input->getOption('factory') ?? false,
            '--controller' => $this->input->getOption('controller') ?? false,
            '--prefix' => $this->input->getOption('prefix') ?? '',
        ];

        if ($args['--interface']) {
            $this->call('gen:interface', $args);
        }
        if ($args['--factory']) {
            $this->call('gen:factory', $args);
        }
        if ($args['--controller']) {
            $this->call('gen:controllers', $args);
        }
        $this->call('gen:service', $args);

        // var_dump($this->name, $this->root, $this->module, $this->getDefaultNamespace());
        // $this->getDefaultNamespace()
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', 'F', InputOption::VALUE_NONE, '是否强制覆盖'],
            ['namespace', 'N', InputOption::VALUE_OPTIONAL, '命名空间 defaule(little)', null],
            ['module', 'M', InputOption::VALUE_OPTIONAL, '命名空间 defaule(null)', null],
            ['interface', 'i', InputOption::VALUE_NONE, '是否生成接口'],
            ['factory', 'f', InputOption::VALUE_NONE, '是否生成工程类'],
            ['controller', 'c', InputOption::VALUE_NONE, '是否生成控制器'],
            ['prefix', 'P', InputOption::VALUE_OPTIONAL, '路由前缀'],
        ];
    }

    protected function getDefaultNamespace(): string
    {
        return $this->root . '\\' . $this->module;
    }
}
