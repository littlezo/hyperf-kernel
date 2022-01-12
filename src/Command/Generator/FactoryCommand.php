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
class FactoryCommand extends GeneratorCommand
{
    use GenTrait;

    protected $layout = 'Factory';

    public function __construct()
    {
        parent::__construct('gen:factory');
        $this->setDescription('Create a new Factory class');
    }

    /**
     * 获取内容.
     *
     * @param mixed $name
     */
    protected function getContent($name)
    {
        $use = [
            'Value' => \Hyperf\Config\Annotation\Value::class,
        ];

        $classNamespace = $this->getNamespace($name);
        $className = str_replace($classNamespace . '\\', '', $name);
        $serviceName = $this->getNameInput('Service');
        $serviceClass = $this->qualifyClass($serviceName, 'Service');
        $content = new PhpFile();
        $content->setStrictTypes();
        $namespace = $content->addNamespace($classNamespace);
        $namespace->addUse($serviceClass);
        foreach ($use as $useClass) {
            $namespace->addUse($useClass);
        }
        $class = $namespace->addClass($className);
        $class->addProperty('enableCache')
            ->addAttribute(\Hyperf\Config\Annotation\Value::class, ['cache.enable', false]);
        $class->addMethod('__invoke')
            ->setReturnType($serviceClass)
            ->setReturnNullable()
            ->setBody(
                str_replace(
                    '%CLASS%',
                    $serviceName,
                    <<<'EOF'
                    $enableCache = $this->enableCache ?? false;
                    return make(%CLASS%::class, compact('enableCache'));
                    EOF
                )
            );

        // echo $content;
        return $content;
    }
}
