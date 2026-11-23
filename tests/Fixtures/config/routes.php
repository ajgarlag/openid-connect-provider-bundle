<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    if (Kernel::VERSION_ID < 80200) {
        $routes
            ->add('app_index', '/')
                ->controller([TemplateController::class, '__invoke'])
                ->defaults([
                    'template' => 'index.html.twig',
                ])
        ;
    } else {
        $routes
            ->add('app_index', '/')
                ->template('index.html.twig')
        ;
    }
};
