<?php
/**
 * Copyright 2023 buexplain@qq.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);

date_default_timezone_set('Asia/Shanghai');

!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));

/**
 * 强制删除文件或文件夹
 * @param string $pathOrFile
 * @return void
 */
function rmf(string $pathOrFile): void
{
    if (is_dir($pathOrFile)) {
        $files = glob($pathOrFile . '/*');
        foreach ($files as $next) {
            if (is_file($next)) {
                unlink($next);
            } elseif (is_dir($next)) {
                rmf($next);
            }
        }
        rmdir($pathOrFile);
    } elseif (is_file($pathOrFile)) {
        unlink($pathOrFile);
    }
}

//hyperf启动扫描进程时，依赖的目录
$dependentDir = [
    [
        BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload',
        BASE_PATH . DIRECTORY_SEPARATOR . 'config',
    ],
    [
        BASE_PATH . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'container',
        BASE_PATH . DIRECTORY_SEPARATOR . 'runtime',
    ],
];
foreach ($dependentDir as $dir) {
    if (!is_dir($dir[0])) {
        mkdir($dir[0], 0755, true);
    }
}

require __DIR__ . '/../vendor/autoload.php';

!defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', Hyperf\Engine\DefaultOption::hookFlags());

(function () use ($dependentDir) {
    Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler(stub: __FILE__));
    Swoole\Runtime::enableCoroutine(true);
    /** @var Psr\Container\ContainerInterface $container */
    $container = new Container((new DefinitionSourceFactory())());
    ApplicationContext::setContainer($container);
    $container->get(Hyperf\Contract\ApplicationInterface::class);
    foreach ($dependentDir as $dir) {
        rmf($dir[1]);
    }
})();
