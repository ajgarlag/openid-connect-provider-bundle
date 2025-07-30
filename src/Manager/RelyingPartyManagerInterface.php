<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\RelyingPartyInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

interface RelyingPartyManagerInterface
{
    public function get(ClientInterface $client): RelyingPartyInterface;

    public function save(RelyingPartyInterface $relyingParty): void;

    public function remove(RelyingPartyInterface $relyingParty): void;
}
