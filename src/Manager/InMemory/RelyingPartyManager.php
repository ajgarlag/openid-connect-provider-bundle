<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\InMemory;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\RelyingParty;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\RelyingPartyInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

final class RelyingPartyManager implements RelyingPartyManagerInterface
{
    /**
     * @var array<string, RelyingPartyInterface>
     */
    private $relyingParties = [];

    public function get(ClientInterface $client): RelyingPartyInterface
    {
        return $this->relyingParties[$client->getIdentifier()] ?? new RelyingParty($client->getIdentifier(), $client);
    }

    public function save(RelyingPartyInterface $relyingParty): void
    {
        $this->relyingParties[$relyingParty->getIdentifier()] = $relyingParty;
    }

    public function remove(RelyingPartyInterface $relyingParty): void
    {
        unset($this->relyingParties[$relyingParty->getIdentifier()]);
    }
}
