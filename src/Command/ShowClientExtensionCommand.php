<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\ClientExtensionInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ajgarlag:openid-connect-provider:show-client-extension', description: 'Show OpenID Connect client extension')]
final class ShowClientExtensionCommand extends Command
{
    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly ClientExtensionManagerInterface $clientExtensionManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Show OpenID Connect client extension')

            ->addArgument('identifier', InputArgument::REQUIRED, 'The client identifier')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null === $client = $this->clientManager->find($input->getArgument('identifier'))) {
            $io->error(\sprintf('OAuth2 client identified as "%s" does not exist.', $input->getArgument('identifier')));

            return 1;
        }

        $clientExtension = $this->clientExtensionManager->get($client);

        $this->drawTable($io, $clientExtension);

        return 0;
    }

    private function drawTable(OutputStyle $io, ClientExtensionInterface $clientExtension): void
    {
        $columns = ['name', 'identifier', 'post logout redirect uri'];
        $rows = [array_combine($columns, [$clientExtension->getClient()->getName(), $clientExtension->getClient()->getIdentifier(), implode(', ', $clientExtension->getPostLogoutRedirectUris())])];
        $io->table($columns, $rows);
    }
}
