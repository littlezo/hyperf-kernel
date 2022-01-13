<?php

declare(strict_types=1);
/**
 * @author @小小只^v^ <littlezov@qq.com>
 */
namespace Littler\Kernel;

use Symfony\Component\Finder\Finder;

class ConfigProvider
{
    public function __invoke(): array
    {
        $annotationsConfig = ['annotations' => [
            'scan' => [
                'paths' => [
                    __DIR__,
                    BASE_PATH . '/little',
                ],
            ],
        ]];
        $autoloadConfig = $this->readModuleConfig();
        return array_merge_recursive($annotationsConfig, ...$autoloadConfig);
    }

    public function readModuleConfig()
    {
        // $configPath = BASE_PATH . '/little/';
        // $config = $this->readConfig($configPath . 'config.php');
        return $this->readPaths([BASE_PATH . '/app/', BASE_PATH . '/little/']);
    }

    private function readPaths(array $paths): array
    {
        $configs = [];
        $finder = new Finder();
        $finder->files()->ignoreUnreadableDirs()->in($paths)->directories(); //name('*.php');
        $config_paths = [];
        foreach ($finder as $file) {
            if (in_array('config', explode('/', $file->getRealPath()))) {
                $config_paths[] = $file->getRealPath() . '/';
            }
        }
        $finder = new Finder();
        $finder->files()->ignoreUnreadableDirs()->in($config_paths)->name('*.php');

        foreach ($finder as $file);
        if (is_array(require $file->getRealPath())) {
            $configs[] = [
                $file->getBasename('.php') => require $file->getRealPath(),
            ];
        }

        return is_array($configs) ? $configs : [];
    }
}
