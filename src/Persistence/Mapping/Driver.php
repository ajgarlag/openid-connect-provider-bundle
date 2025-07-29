<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Persistence\Mapping;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientData;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use League\Bundle\OAuth2ServerBundle\Model\Client;

final class Driver implements MappingDriver
{
    public function __construct(private readonly string $tablePrefix = 'openid_connect_')
    {
    }

    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
        if (!$metadata instanceof ORMClassMetadata) {
            throw new \InvalidArgumentException(\sprintf('"$metadata" must be an instance of "%s"', ORMClassMetadata::class));
        }

        match ($className) {
            ClientData::class => $this->buildClientDataMetadata($metadata),
            default => throw new \RuntimeException(\sprintf('%s cannot load metadata for class %s', self::class, $className)),
        };
    }

    public function getAllClassNames(): array
    {
        return [
            ClientData::class,
        ];
    }

    public function isTransient($className): bool
    {
        return false;
    }

    /**
     * @param ORMClassMetadata<ClientData> $metadata
     */
    private function buildClientDataMetadata(ORMClassMetadata $metadata): void
    {
        (new ClassMetadataBuilder($metadata))
            ->setTable($this->tablePrefix . 'client_data')
            ->createField('identifier', 'string')->makePrimaryKey()->length(80)->option('fixed', true)->build()
            ->createField('postLogoutRedirectUris', 'oauth2_redirect_uri')->nullable(true)->build()
            ->createManyToOne('client', Client::class)->addJoinColumn('client', 'identifier', false, false, 'CASCADE')->build()
        ;
    }
}
