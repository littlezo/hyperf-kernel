<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\CodeGen\Project;
use Hyperf\Utils\Str;
use Nette\PhpGenerator\Dumper;
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

        $dumper = new Dumper();

        $content = '<?php' . PHP_EOL . PHP_EOL . 'return [];';
        $dependenciesName = $this->getDefaultNamespace() . '\\autoload\\dependencies';
        $dependenciesPath = $this->getPath($dependenciesName);
        if (! file_exists($dependenciesPath)) {
            $this->makeDirectory($dependenciesPath);
            file_put_contents($dependenciesPath, $content);
        }
        $dependencie = require $dependenciesPath;

        $interface = $this->qualifyClass('Interface');
        if ($this->input->getOption('factory')) {
            $factory = $this->qualifyClass('Factory');
        } else {
            $factory = $this->qualifyClass('Service');
        }

        $dependencies = $dependencie;

        // foreach ($dependencie as $key => $value) {
        //     if (class_exists($key) && class_exists($value)) {
        //         $dependencies[$key] = $value;
        //     }
        // }
        $dependencies[$interface] = $factory;

        $content = sprintf('<?php' . PHP_EOL . PHP_EOL . 'return %s;', $dumper->dump($dependencies));
        file_put_contents($dependenciesPath, $content);

        $this->output->writeln(sprintf('<info>%s</info>', $dependenciesName . ' write successfully.'));
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

    protected function qualifyClass($layout)
    {
        $name = $this->name;

        $name = str_replace('/', '\\', $name);
        // var_dump($name);
        $namespace = $this->input->getOption('namespace');
        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace();
        }
        return $namespace . "\\{$layout}\\" . $name . $layout;
    }

    protected function getDefaultNamespace(): string
    {
        if ($this->module) {
            return $this->root . '\\' . $this->module;
        }
        return $this->root;
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $project = new Project();
        return BASE_PATH . '/' . $project->path($name);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        return $path;
    }
}
