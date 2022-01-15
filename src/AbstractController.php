<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel;

use Firstphp\Wsdebug\Wsdebug;
use Hyperf\Config\Annotation\Value;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractController
{
    #[Inject]
    protected Wsdebug $debug;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected CacheInterface $cache;

    #[Value('app_env')]
    protected $app_env;

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->container = $container;

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $cache = $container->get(CacheInterface::class);
        $this->cache = $cache;
    }

    public function success($data = [], $message = 'success', $code = 200)
    {
        if ($data instanceof PaginatorInterface) {
            return $this->response->json([
                'code' => $code,
                'type' => 'successed',
                'is_debug' => $this->app_env,
                'timestamp' => time(),
                'data' => [
                    'current' => $data->currentPage(),
                    'last' => $data->lastPage(),
                    'total' => $data->total(),
                    'count' => $data->count(),
                    'first' => $data->onFirstPage(),
                    'more' => $data->hasMorePages(),
                    'data' => $data->items(),
                ],
                'message' => $message,
            ]);
        }
        return $this->response->json([
            'code' => $code,
            'type' => 'successed',
            'is_debug' => $this->app_env,
            'timestamp' => time(),
            'data' => $data ?: [],
            'message' => $message,
        ]);
    }

    protected function fail($msg = '哦！失败了', $code = 500)
    {
        return $this->response->json([
            'code' => $code,
            'message' => $msg,
            'type' => 'failed',
            'timestamp' => time(),
        ]);
    }

    protected function dump(...$vars): void
    {
        foreach ($vars as $v) {
            dump($v);
        }
    }
}
