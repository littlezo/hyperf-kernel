<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Nette\PhpGenerator\PhpFile;

/**
 * @Command
 */
#[Command]
class InterfaceCommand extends GeneratorCommand
{
    use GenTrait;

    protected $layout = 'Interface';

    public function __construct()
    {
        parent::__construct('gen:interface');
        $this->setDescription('Create a new Interface class');
    }

    /**
     * 获取内容.
     *
     * @param mixed $name
     */
    protected function getContent($name)
    {
        $classNamespace = $this->getNamespace($name);
        $className = str_replace($classNamespace . '\\', '', $name);
        // var_dump($classNamespace, $className);
        $content = new PhpFile();
        $content->setStrictTypes();
        $namespace = $content->addNamespace($classNamespace);

        $class = $namespace->addClass($className);
        $class->setInterface();
        $method = $class->addMethod('debug')
            // ->addAttribute('调试')
            // ->addComment('@param array $args 传入参数')
            // ->addComment('@return mixed')
            ->setPublic()
            // ->setReturnType('mixed')
            ->setReturnNullable();
        $method->addParameter('args', null)
            ->setType('array');
        return $content;
    }
}
