# 参数描述类

本包解决的问题是，参数通过map传递导致参数无法被编辑器识别的问题。

注意，本包只能在[hyperf](https://github.com/hyperf/hyperf)框架下使用，其它框架需要实现。

安装命令：`composer require buexplain/arg`

## 使用示例
以注册接口为例子，做个简单的示例。

第一步：构造一个注册接口需要的参数描述。

```php
<?php

namespace App\Arg;

use Arg\Attr\ArgValidationAttr;
use Arg\BaseArgForHyperf;

/**
 * 注册一个用户
 */
class RegisterArg extends BaseArgForHyperf
{
    /**
     * @var string 账号
     */
    #[ArgValidationAttr('required')]
    public string $account;

    /**
     * @var string 密码
     */
    #[ArgValidationAttr('required')]
    public string $password;

    /**
     * @var string 验证码
     */
    #[ArgValidationAttr('required')]
    public string $verification_code;
}
```

第二步：编写注册接口的控制器
```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Arg\RegisterArg;
use App\Services\RegisterService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexController extends AbstractController
{
    /**
     * 注册用户
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(): array
    {
        //初始化参数类
        $registerArg = new RegisterArg($this->request->all());
        //校验数据
        $msgBag = $registerArg->validate();
        if ($msgBag->isNotEmpty()) {
            //校验失败，返回错误信息
            return [
                'code' => 1,
                'message' => $msgBag->first(),
            ];
        }
        //调用service层的注册逻辑，执行注册动作，并返回注册结果
        return [
            'code' => 0,
            'data' => RegisterService::create($registerArg),
            'message' => '注册成功',
        ];
    }
}
```

第三步：实现注册逻辑
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Arg\RegisterArg;

class RegisterService
{
    /**
     * 注册用户
     * @param RegisterArg $arg
     * @return array
     */
    public static function create(RegisterArg $arg): array
    {
        //开始注册逻辑，这里假设注册成功后返回账号信息
        return ['account' => $arg->account];
    }
}
```


## 运行测试用例

Windows下：

`swoole-cli.exe /cygdrive/c/buexplain/arg/vendor/bin/co-phpunit --prepend tests/bootstrap.php --configuration phpunit.xml --log-events-verbose-text phpunit.log`

Linux：
`composer test`

