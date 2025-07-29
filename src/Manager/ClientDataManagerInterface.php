<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientDataInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

interface ClientDataManagerInterface
{
    public function get(ClientInterface $client): ClientDataInterface;

    public function save(ClientDataInterface $clientData): void;

    public function remove(ClientDataInterface $clientData): void;
}
