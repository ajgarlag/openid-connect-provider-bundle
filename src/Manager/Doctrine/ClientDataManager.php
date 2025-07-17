<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\Doctrine;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientDataManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientData;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientDataInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

final class ClientDataManager implements ClientDataManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function get(ClientInterface $client): ClientDataInterface
    {
        $repository = $this->entityManager->getRepository(ClientData::class);

        return $repository->findOneBy(['client' => $client]) ?? new ClientData($client->getIdentifier(), $client);
    }

    public function save(ClientDataInterface $clientData): void
    {
        $this->entityManager->persist($clientData);
        $this->entityManager->flush();
    }

    public function remove(ClientDataInterface $clientData): void
    {
        $this->entityManager->remove($clientData);
        $this->entityManager->flush();
    }
}
