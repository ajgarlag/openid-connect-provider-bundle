<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\Doctrine;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\RelyingParty;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\RelyingPartyInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

final class RelyingPartyManager implements RelyingPartyManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function get(ClientInterface $client): RelyingPartyInterface
    {
        $repository = $this->entityManager->getRepository(RelyingParty::class);

        return $repository->findOneBy(['client' => $client]) ?? new RelyingParty($client->getIdentifier(), $client);
    }

    public function save(RelyingPartyInterface $relyingParty): void
    {
        $this->entityManager->persist($relyingParty);
        $this->entityManager->flush();
    }

    public function remove(RelyingPartyInterface $relyingParty): void
    {
        $this->entityManager->remove($relyingParty);
        $this->entityManager->flush();
    }
}
