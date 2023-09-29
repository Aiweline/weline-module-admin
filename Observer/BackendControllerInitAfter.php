<?php

namespace Weline\Admin\Observer;

use Weline\Admin\Model\BackendUserData;
use Weline\Backend\Model\BackendUser;
use Weline\Backend\Session\BackendSession;
use Weline\Framework\Event\Event;
use Weline\Framework\Event\ObserverInterface;
use Weline\Framework\Http\Cookie;
use Weline\Framework\Http\Request;
use Weline\Framework\Http\Url;
use Weline\Framework\Manager\MessageManager;
use Weline\Framework\Manager\ObjectManager;

class BackendControllerInitAfter implements ObserverInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    private function getSession(): BackendSession
    {
        return ObjectManager::getInstance(BackendSession::class);
    }

    /**
     * @inheritDoc
     */
    public function execute(Event $event)
    {
        # 检测记住我
        if ($token = Cookie::get('w_urt')) {
            /**@var BackendUserData $backendUserData */
            $backendUserData = ObjectManager::getInstance(BackendUserData::class);
            $backendUserData->load($backendUserData::fields_token, $token);
            if ($backendUserData->getId() and $backendUserData->getData($backendUserData::fields_token_expire_time) < time()) {
                $backendUserData->setData($backendUserData::fields_token, '')
                    ->setData($backendUserData::fields_token_expire_time, 0);
                ObjectManager::getInstance(MessageManager::class)->addWarning(__('记住登录已过期，请重新登录！'));
                $this->getSession()->logout();
            } elseif ($user_id = $backendUserData->getId()) {
                # SESSION登录用户
                $adminUser = ObjectManager::getInstance(BackendUser::class)->load($user_id);
                if ($adminUser->getId()) {
                    $this->getSession()->login($adminUser, (int)$adminUser->getId());
                    $adminUser->setSessionId($this->getSession()->getSessionId())
                        ->setLoginIp($this->request->clientIP());
                    # 重置 尝试登录次数
                    $adminUser->resetAttemptTimes()->save();
                } else {
                    ObjectManager::getInstance(MessageManager::class)->addWarning(__('用户不存在！'));
                }
            }
        }
        # 设置referer
        if (!$this->request->isBackend()) {
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
            /**@var Url $urlBuilder*/
            $urlBuilder = ObjectManager::getInstance(Url::class);
            $this->getSession()->setData('referer', $urlBuilder->getCurrentUrl());
        }
    }
}
