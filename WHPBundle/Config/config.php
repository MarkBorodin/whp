<?php

declare(strict_types=1);

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use MauticPlugin\WHPBundle\Integration\WHPIntegration;

return [
    'name'        => 'WHP',
    'description' => 'Enables WHP',
    'version'     => '1.0.0',
    'author'      => 'Mark Borodin',
    'routes'      => [
        'main'   => [],
        'public' => [
            'whp.request' => [
                'path'       => '/whp_request',
                'controller' => 'WHPBundle:Requests:whpRequest',
                'method'     => 'POST',
            ],
        ],
        'api'    => [],
    ],
    'menu'        => [],
    'services' => [
        'integrations' => [
            'mautic.integration.whp' => [
                'class' => \MauticPlugin\WHPBundle\Integration\WHPIntegration::class,
                'tags'  => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'mautic.integration.whp.configuration' => [
                'class'     => \MauticPlugin\WHPBundle\Integration\Support\ConfigSupport::class,
                'tags'      => [
                    'mautic.config_integration',
                ],
            ],
        ],
        'others' => [
            'whp.integration.config' => [
                'class'     => \MauticPlugin\WHPBundle\Integration\Config::class,
                'arguments' => [
                    'mautic.integrations.helper',
                ],
            ],
        ],
    ],
    'parameters' => [],
];
