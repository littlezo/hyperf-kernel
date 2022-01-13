<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel\Command\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Str;
use Littler\Kernel\AbstractController;
use Nette\PhpGenerator\PhpFile;

/**
 * @Command
 */
#[Command]
class ControllerCommand extends GeneratorCommand
{
    use GenTrait;

    protected $layout = 'Controller';

    public function __construct()
    {
        parent::__construct('gen:controllers');
        $this->setDescription('Create a new Controller class');
    }

    /**
     * 获取内容.
     *
     * @param mixed $name
     */
    protected function getContent($name)
    {
        $use = [
            'AbstractController' => AbstractController::class,
            'Inject' => Inject::class,
            'Controller' => Controller::class,
            'RequestMapping' => RequestMapping::class,
            'RequestInterface' => RequestInterface::class,
            'ResponseInterface' => ResponseInterface::class,
        ];

        $classNamespace = $this->getNamespace($name);
        $className = str_replace($classNamespace . '\\', '', $name);

        $content = new PhpFile();
        $content->setStrictTypes();
        $namespace = $content->addNamespace($classNamespace);

        foreach ($use as $useClass) {
            $namespace->addUse($useClass);
        }
        $class = $namespace->addClass($className);
        $class->addExtend(AbstractController::class);
        // 注入接口
        if ($this->input->getOption('interface')) {
            $injectName = $this->getNameInput('Interface');
            $injectClass = $this->qualifyClass($injectName, 'Interface');
            $namespace->addUse($injectClass);
        } else {
            $injectName = $this->getNameInput('Service');
            $injectClass = $this->qualifyClass($injectName, 'Service');
            $namespace->addUse($injectClass);
        }
        $nameInput = Str::snake($this->getNameInput('', true));
        // 注入路由
        $prefix = $this->getPrefix();
        $class->addAttribute(Controller::class, ['prefix' => "/{$prefix}/{$nameInput}"]);
        $class->addProperty($nameInput)
            ->setProtected()
            ->addAttribute(Inject::class)
            ->setType($injectClass);
        // ->addComment('@Inject()')
        // ->addComment('@var ' . $injectName)

        $method = $class->addMethod('debug')
            // ->setReturnType($nameInput)
            ->setReturnNullable()
            ->setBody(
                str_replace(
                    '%nameInput%',
                    $nameInput,
                    <<<'EOF'
                    return $this->success($this->%nameInput%->debug($request->all()));
                    EOF
                )
            );
        $method->addAttribute(RequestMapping::class, ['path' => 'debug', 'methods' => 'get,post']);
        $method->addParameter('request')
            ->setType(RequestInterface::class);
        $method->addParameter('response')
            ->setType(ResponseInterface::class);
        // echo $content;
        return $content;
    }
}
