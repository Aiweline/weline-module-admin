<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 */

namespace Weline\Admin\Controller;

use Weline\Framework\Http\Request;
use Weline\Framework\Router\RouterInterface;

class Router implements RouterInterface
{
    public static function process(string &$path, array &$rule)
    {
        $flag = '/admin/Template/Upzet/';
        if (str_starts_with(strtolower($path), strtolower($flag))) {
            $rule = ['template' => str_replace(strtolower($flag), '', strtolower($path))];
            $path = $flag;
        }
    }
}
