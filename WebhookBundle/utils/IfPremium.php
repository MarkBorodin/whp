<?php

namespace Mautic\WebhookBundle\utils;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MauticPlugin\WHPBundle\Integration\WHPIntegration;

class IfPremium extends CommonController
{

    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    public function checkIfPremium()
    {
        $integrationHelper = $this->factory->getHelper('integration');
        $WHPIntegration = $integrationHelper->getIntegrationObject(WHPIntegration::NAME);
        if($WHPIntegration) {
            try {
                $integration = $WHPIntegration->getIntegrationConfiguration();
                return $integration->getIsPublished() ?: false;
            } catch (IntegrationNotFoundException $e) {
                return false;
            }
        }
        return false;
    }
}