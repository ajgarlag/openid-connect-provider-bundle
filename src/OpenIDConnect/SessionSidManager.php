<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class SessionSidManager
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
    ) {
    }

    private function getFirewallConfig(): FirewallConfig
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            throw new \RuntimeException('No current request available to get firewall context.');
        }

        if (null === $firewallConfig = $this->security->getFirewallConfig($request)) {
            throw new \RuntimeException('No firewall context for current request.');
        }

        return $firewallConfig;
    }

    private function getFirewallContext(FirewallConfig $firewallConfig): string
    {
        return $firewallConfig->getContext() ?? $firewallConfig->getName();
    }

    /**
     * @return non-empty-string
     */
    public function getOrGenerateSid(): string
    {
        $firewallConfig = $this->getFirewallConfig();
        if (null !== $sid = $this->getSid($firewallConfig)) {
            return $sid;
        }

        $sid = bin2hex(random_bytes(16));
        $firewallContext = $this->getFirewallContext($firewallConfig);
        $this->requestStack->getSession()->set('ajgarlag.openid_connect_provider.sid.' . $firewallContext, $sid);

        return $sid;
    }

    /**
     * @return non-empty-string|null
     */
    public function getSid(FirewallConfig $firewallConfig): ?string
    {
        $firewallContext = $this->getFirewallContext($firewallConfig);
        $session = $this->requestStack->getSession();

        if ($session->has('ajgarlag.openid_connect_provider.sid.' . $firewallContext) && null !== $sid = $session->get('ajgarlag.openid_connect_provider.sid.' . $firewallContext)) {
            if (\is_string($sid) && '' !== $sid) {
                return $sid;
            }
        }

        return null;
    }
}
