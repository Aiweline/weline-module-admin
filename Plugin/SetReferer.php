<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 作者：Admin
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 * 日期：2023/5/1 11:05:55
 */

namespace Weline\Admin\Plugin;

use Weline\Admin\Observer\BackendWhitelistUrl;
use Weline\Framework\Http\Request;
use Weline\Framework\Http\Url;
use Weline\Framework\Router\Core;
use Weline\Framework\Session\Session;

class SetReferer
{
    private $is_admin = false;
    private Request $request;
    private Session $session;
    private Url $_url;

    public function __construct(Request $request, Session $session, Url $url)
    {
        $this->request = $request;
        $this->session = $session;
        $this->_url = $url;
        $this->is_admin = $request->isBackend();
    }

    public function beforeStart(Core $route)
    {
        if (!$this->is_admin) {
            return;
        }
        // 绕过ajax请求
        if ($this->request->isAjax()) {
            return;
        }
        // 绕过ajax请求
        if ($this->request->isIframe()) {
            return;
        }
        $white_urls = BackendWhitelistUrl::white_urls;
        $white_urls[] = ['path' => 'admin/login/logout'];
        foreach ($white_urls as &$white_url) {
            $white_url = $white_url['path'];
        }
        if (!in_array(trim($this->request->getRouteUrlPath(), '/'), $white_urls)) {
            $this->session->setData('referer', $this->_url->getCurrentUrl());
        }
    }
}
