<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('app_index', '/')
            ->controller([TemplateController::class, '__invoke'])
            ->defaults([
                'template' => 'index.html.twig',
            ])
    ;
};
