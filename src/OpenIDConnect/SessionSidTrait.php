<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

trait SessionSidTrait
{
    /**
     * @return non-empty-string
     */
    public function getOrGenerateSid(SessionInterface $session): string
    {
        if (null !== $sid = $this->getSid($session)) {
            return $sid;
        }

        $sid = bin2hex(random_bytes(16));
        $session->set('ajgarlag.openid_connect_provider.sid', $sid);

        return $sid;
    }

    /**
     * @return non-empty-string|null
     */
    public function getSid(SessionInterface $session): ?string
    {
        if ($session->has('ajgarlag.openid_connect_provider.sid') && null !== $sid = $session->get('ajgarlag.openid_connect_provider.sid')) {
            if (\is_string($sid) && '' !== $sid) {
                return $sid;
            }
        }

        return null;
    }
}
