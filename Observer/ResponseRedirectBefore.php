<?php

namespace Weline\Admin\Observer;

use Weline\Framework\Event\Event;
use Weline\Framework\Event\ObserverInterface;
use Weline\Framework\Http\Cookie;
use Weline\Framework\Http\Request;
use Weline\Framework\Manager\ObjectManager;
use Weline\Framework\Session\Session;

class ResponseRedirectBefore implements ObserverInterface
{
    /**
     * @var Request
     */
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function execute(Event $event)
    {
        $data = $event->getData('data');
        $url = $data->getUrl();
        $code = $data->getCode();
        $path = $this->request->getRouteUrlPath($url);
        if ($this->request->isBackend() and ($code == 302) and $this->request->isGet() and ($path === 'admin/login')) {
            ObjectManager::getInstance(Session::class)->setData('backend_login_referer', $this->request->getUrlBuilder()->getCurrentUrl());
        }
    }
}
