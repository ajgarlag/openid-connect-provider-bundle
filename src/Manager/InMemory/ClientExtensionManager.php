<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\InMemory;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientExtension;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientExtensionInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

final class ClientExtensionManager implements ClientExtensionManagerInterface
{
    /**
     * @var array<string, ClientExtensionInterface>
     */
    private $clientExtensions = [];

    public function get(ClientInterface $client): ClientExtensionInterface
    {
        return $this->clientExtensions[$client->getIdentifier()] ?? new ClientExtension($client->getIdentifier(), $client);
    }

    public function save(ClientExtensionInterface $clientExtension): void
    {
        $this->clientExtensions[$clientExtension->getIdentifier()] = $clientExtension;
    }

    public function remove(ClientExtensionInterface $clientExtension): void
    {
        unset($this->clientExtensions[$clientExtension->getIdentifier()]);
    }
}
