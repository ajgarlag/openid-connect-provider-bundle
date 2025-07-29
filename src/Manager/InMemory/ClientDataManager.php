<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\InMemory;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientDataManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientData;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientDataInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

final class ClientDataManager implements ClientDataManagerInterface
{
    /**
     * @var array<string, ClientDataInterface>
     */
    private $clientDataMap = [];

    public function get(ClientInterface $client): ClientDataInterface
    {
        return $this->clientDataMap[$client->getIdentifier()] ?? new ClientData($client->getIdentifier(), $client);
    }

    public function save(ClientDataInterface $clientData): void
    {
        $this->clientDataMap[$clientData->getIdentifier()] = $clientData;
    }

    public function remove(ClientDataInterface $clientData): void
    {
        unset($this->clientDataMap[$clientData->getIdentifier()]);
    }
}
