<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Utils\Str;
use Nette\PhpGenerator\PhpFile;

/**
 * @Command
 */
#[Command]
class ServiceCommand extends GeneratorCommand
{
    use GenTrait;

    protected $layout = 'Service';

    public function __construct()
    {
        parent::__construct('gen:service');
        $this->setDescription('Create a new service class');
    }

    /**
     * 获取内容.
     *
     * @param mixed $name
     */
    protected function getContent($name)
    {
        $use = [
            'LoggerFactory' => \Hyperf\Logger\LoggerFactory::class,
            'ContainerInterface' => \Psr\Container\ContainerInterface::class,
            'CacheInterface' => \Psr\SimpleCache\CacheInterface::class,
        ];

        $classNamespace = $this->getNamespace($name);
        $className = str_replace($classNamespace . '\\', '', $name);

        $content = new PhpFile();
        $content->setStrictTypes();
        $namespace = $content->addNamespace($classNamespace);
        $namespace->addUse(\Hyperf\Di\Annotation\Inject::class);
        foreach ($use as $useClass) {
            $namespace->addUse($useClass);
        }

        $class = $namespace->addClass($className);

        if ($this->input->getOption('interface')) {
            $interfaceName = $this->getNameInput('Interface');
            $interfaceClass = $this->qualifyClass($interfaceName, 'Interface');
            $namespace->addUse($interfaceClass);
            $class->addImplement($interfaceClass);
            // $use[$interfaceName] = $interfaceClass;
        }
        foreach ($use as $key => $value) {
            $kay_name = str_replace('Interface', '', $key);
            $kay_name = str_replace('Factory', '', $kay_name);
            $kay_name = str_replace('Service', '', $kay_name);
            $class->addProperty(Str::snake($kay_name))
                ->setProtected()
                ->addAttribute($value)
                ->addComment('@Inject()')
                ->addComment('@var ' . $namespace->simplifyName($value));
        }
        $method = $class->addMethod('debug')
            ->addComment('调试')
            ->addComment('@param array $args 传入参数')
            ->addComment('@return mixed')
            ->setReturnType('mixed')
            ->setPublic()
            ->setReturnNullable()
            ->setBody(
                str_replace(
                    '%CLASS%',
                    $className,
                    <<<'EOF'
                    $logger = $this->cache->set('%CLASS%Debug', '%CLASS% Debug' . json_encode($args) . date('Y-m-d H:i:s'));
                    $logger = $this->logger->get('default');
                    $logger->info('%CLASS%  Debug' . json_encode($args) . date('Y-m-d H:i:s'));
                    return '%CLASS%  Debug';
                    EOF
                )
            );

        $method->addParameter('args', null)
            ->setType('array');
        // echo $content;
        return $content;
    }
}
