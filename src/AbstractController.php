<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel;

use Firstphp\Wsdebug\Wsdebug;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var Wsdebug
     */
    protected $debug;

    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @Inject
     * @var CacheInterface
     *@var Cacheable
     */
    protected $cache;

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->container = $container;

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $cache = $container->get(CacheInterface::class);
        $this->cache = $cache;
    }

    public function success($data = [], $msg = 'success', $code = 200)
    {
        if ($data instanceof PaginatorInterface) {
            return $this->response->json([
                'code' => $code,
                'type' => 'successed',
                'timestamp' => time(),
                'data' => [
                    // 'current_page' => $data->current_page,
                    // 'data' => $data->data,
                    // 'last_page' => $data->last_page,
                    // 'current_page' => $data->current_page,
                    // 'total' => $data->total,
                    'orgin' => $data,
                ],
                'message' => $msg,
            ]);
        }
        return $this->response->json([
            'code' => $code,
            'type' => 'successed',
            'timestamp' => time(),
            'data' => $data ?: [],
            'message' => $msg,
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
