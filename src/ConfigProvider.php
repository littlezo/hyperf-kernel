<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel;

use Littler\Kernel\Listener\DbQueryExecutedListener;
use Monolog\Formatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler;
use Monolog\Logger;
use Symfony\Component\Finder\Finder;

class ConfigProvider
{
    public function __invoke(): array
    {
        $defaultConfig = $this->defaultConfig();
        $autoloadConfig = $this->readModuleConfig();
        return array_merge_recursive($defaultConfig, ...$autoloadConfig);
    }

    public function defaultConfig()
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                        BASE_PATH . '/little',
                    ],
                ],
            ],
            'listeners' => [
                DbQueryExecutedListener::class,
            ],
            'logger' => [
                'sql' => [
                    'handlers' => [
                        [
                            'class' => Handler\RotatingFileHandler::class,
                            'constructor' => [
                                'filename' => BASE_PATH . '/runtime/logs/sql.log',
                                'level' => Logger::INFO,
                            ],
                            'formatter' => [
                                'class' => Formatter\LineFormatter::class,
                                'constructor' => [
                                    'format' => null,
                                    'dateFormat' => 'Y-m-d H:i:s',
                                    'allowInlineLineBreaks' => true,
                                ],
                            ],
                        ],
                        [
                            'class' => Handler\RotatingFileHandler::class,
                            'constructor' => [
                                'filename' => BASE_PATH . '/runtime/logs/sql-debug.log',
                                'level' => Logger::DEBUG,
                            ],
                            'formatter' => [
                                'class' => JsonFormatter::class,
                                'constructor' => [
                                    'batchMode' => JsonFormatter::BATCH_MODE_JSON,
                                    'appendNewline' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function readModuleConfig()
    {
        return $this->readPaths([BASE_PATH . '/app/', BASE_PATH . '/little/']);
    }

    private function readPaths(array $paths): array
    {
        $configs = [];
        $finder = new Finder();
        $finder->files()->ignoreUnreadableDirs()->in($paths)->directories(); //name('*.php');
        $config_paths = [];
        foreach ($finder as $file) {
            if (in_array('autoload', explode('/', $file->getRealPath()))) {
                $config_paths[] = $file->getRealPath() . '/';
            }
        }

        if ($config_paths) {
            $finder = new Finder();
            $finder->files()->ignoreUnreadableDirs()->in($config_paths)->name('*.php');

            foreach ($finder as $file) {
                if (file_exists($file->getRealPath()) && is_array(require $file->getRealPath())) {
                    $configs[] = [
                        $file->getBasename('.php') => require $file->getRealPath(),
                    ];
                }
            }
        }

        return is_array($configs) ? $configs : [];
    }
}
