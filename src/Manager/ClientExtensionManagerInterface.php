<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientExtensionInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

interface ClientExtensionManagerInterface
{
    public function get(ClientInterface $client): ClientExtensionInterface;

    public function save(ClientExtensionInterface $clientExtension): void;

    public function remove(ClientExtensionInterface $clientExtension): void;
}
