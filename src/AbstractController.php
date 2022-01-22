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
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractController
{
    // #[Inject]
    // protected Wsdebug $debug;

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

    private $httpCode = 200;

    private $headers = [
        'Author' => '@little^V^!',
    ];

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->container = $container;

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $cache = $container->get(CacheInterface::class);
        $this->cache = $cache;
        $this->headers += ['node' => swoole_get_local_ip()['eth0'], 'timestamp' => time()];
    }

    /**
     * 设置http返回码
     * @param int $code http返回码
     * @return $this
     */
    final public function setHttpCode(int $code = 200): self
    {
        $this->httpCode = $code;
        return $this;
    }

    /**
     * 设置返回头部header值
     * @param $value
     * @return $this
     */
    public function addHttpHeader(string $key, $value): self
    {
        $this->headers += [$key => $value];
        return $this;
    }

    /**
     * 批量设置头部返回.
     * @param array $headers header数组：[key1 => value1, key2 => value2]
     * @return $this
     */
    public function addHttpHeaders(array $headers = []): self
    {
        $this->headers += $headers;
        return $this;
    }

    public function success($data = [], $message = 'success', $code = 200)
    {
        if ($data instanceof PaginatorInterface) {
            return $this->send([
                'code' => $code,
                'type' => 'successed',
                'is_debug' => $this->app_env,
                'timestamp' => time(),
                'data' => [
                    'page' => $data->currentPage(),
                    'last' => $data->lastPage(),
                    'total' => $data->total(),
                    'size' => $data->perPage(),
                    'first' => $data->onFirstPage(),
                    'more' => $data->hasMorePages(),
                    'data' => $data->items(),
                ],
                'message' => $message,
            ]);
        }
        return $this->send([
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
        return $this->setHttpCode($this->httpCode == 200 ? 400 : $this->httpCode)->send([
            'code' => $code,
            'message' => $msg,
            'type' => 'failed',
            'timestamp' => time(),
        ]);
    }

    /**
     * @return null|mixed|ResponseInterface
     */
    protected function response(): ResponseInterface
    {
        $response = $this->response;
        foreach ($this->headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }
        return $response;
    }

    protected function dump(...$vars): void
    {
        foreach ($vars as $v) {
            dump($v);
        }
    }

    /**
     * @param null|array|Arrayable|Jsonable|string $response
     */
    private function send($response): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream($response));
        }

        if (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream(Json::encode($response)));
        }

        if ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream((string) $response));
        }

        return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream((string) $response));
    }
}
