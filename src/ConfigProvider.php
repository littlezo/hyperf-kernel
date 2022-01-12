<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
