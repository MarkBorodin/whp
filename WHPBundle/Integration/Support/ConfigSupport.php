<?php

declare(strict_types=1);

namespace MauticPlugin\WHPBundle\Integration\Support;


use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\WHPBundle\Form\Type\ConfigAuthType;
use MauticPlugin\WHPBundle\Integration\WHPIntegration;


class ConfigSupport extends WHPIntegration implements ConfigFormInterface, ConfigFormAuthInterface
{
    use DefaultConfigFormTrait;

    /**
     * {@inheritdoc}
     */
    public function getAuthConfigFormName(): string
    {
        return ConfigAuthType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFeatureSettingsConfigFormName(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSyncConfigObjects(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSyncMappedObjects(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredFieldsForMapping(string $object): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionalFieldsForMapping(string $object): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllFieldsForMapping(string $object): array
    {
        return [];
    }
}
