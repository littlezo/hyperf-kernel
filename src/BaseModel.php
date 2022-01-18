<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

abstract class BaseModel extends Model implements CacheableInterface
{
    use Cacheable;

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public const DELETED_AT = 'delete_time';

    public $timestamps = true;

    #[Inject]
    protected RequestInterface $request;

    /**
     * 软删除默认值
     */
    protected $defaultSoftDelete = 0;

    /**
     * 排除不存在字段.
     *
     * @param array $params
     */
    public function filterNotExist($params = [])//: Model
    {
        $requestParams = $this->request->all();
        $params = array_merge($params, $requestParams);
        // return$params ;
        if (empty($params) && empty($requestParams)) {
            return $this;
        }
        $where = [];
        foreach ($params as $field => $value) {
            // 排除不存在字段dd
            if (in_array($field, $this->fillable, true, )) {
                if (is_array($params[$field])) {
                    $where[] = [$field, $params[$field][0], $params[$field]];
                } else {
                    $where[] = [$field, $value];
                }
            }
        }

        return $where;
    }
}
