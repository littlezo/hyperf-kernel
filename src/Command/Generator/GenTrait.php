<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Littler\Kernel\Command\Generator;

use Symfony\Component\Console\Input\InputOption;

trait GenTrait
{
    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        return $this->getContent($name);
    }

    protected function getNameInput($layout = null, $ext = false)
    {
        if ($ext) {
            return trim($this->input->getArgument('name'));
        }
        if (! $layout) {
            $layout = $this->layout;
        }
        return trim($this->input->getArgument('name')) . $layout ?? $this->layout;
    }

    protected function qualifyClass($name, $layout = null)
    {
        if (! $layout) {
            $layout = $this->layout;
        }
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $namespace = $this->input->getOption('namespace');
        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace();
        }
        return $namespace . "\\{$layout}\\" . $name;
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? '';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Factory';
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
        ];
    }
}
