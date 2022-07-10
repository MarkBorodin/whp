<?php

namespace Mautic\WebhookBundle\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;

/**
 * Class Header.
 */
class Header extends FormEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var Webhook
     */
    private $webhook;

    /**
     * @var string
     */
    private $headerKey;

    /**
     * @var string
     */
    private $headerValue;


    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('webhook_headers')
            ->setCustomRepositoryClass(HeaderRepository::class)
            ->addId();

        $builder->createManyToOne('webhook', 'Webhook')
            ->inversedBy('headers')
            ->addJoinColumn('webhook_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->addNullableField('headerKey', Type::TEXT, 'header_key');
        $builder->addNullableField('headerValue', Type::TEXT, 'header_value');
    }

    /**
     * @return Webhook
     */
    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    public function setWebhook($webhook): void
    {
        $this->webhook = $webhook;
    }

    /**
     * @return string
     */
    public function getHeaderKey(): ?string
    {
        return $this->headerKey;
    }

    /**
     * @param string $headerKey
     */
    public function setHeaderKey(string $headerKey): void
    {
        $this->headerKey = $headerKey;
    }

    /**
     * @return string
     */
    public function getHeaderValue(): ?string
    {
        return $this->headerValue;
    }

    /**
     * @param string $headerValue
     */
    public function setHeaderValue(string $headerValue): void
    {
        $this->headerValue = $headerValue;
    }

}