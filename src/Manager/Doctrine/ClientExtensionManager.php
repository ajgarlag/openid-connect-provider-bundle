<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\Doctrine;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientExtension;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientExtensionInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;

final class ClientExtensionManager implements ClientExtensionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function get(ClientInterface $client): ClientExtensionInterface
    {
        $repository = $this->entityManager->getRepository(ClientExtension::class);

        return $repository->findOneBy(['client' => $client]) ?? new ClientExtension($client->getIdentifier(), $client);
    }

    public function save(ClientExtensionInterface $clientExtension): void
    {
        $this->entityManager->persist($clientExtension);
        $this->entityManager->flush();
    }

    public function remove(ClientExtensionInterface $clientExtension): void
    {
        $this->entityManager->remove($clientExtension);
        $this->entityManager->flush();
    }
}
