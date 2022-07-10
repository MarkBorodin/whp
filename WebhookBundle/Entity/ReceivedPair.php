<?php

namespace Mautic\WebhookBundle\Entity;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;

/**
 * Class ReceivedPair.
 */
class ReceivedPair extends FormEntity
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
    private $receivedField;

    /**
     * @var string
     */
    private $subjectField;


    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('webhook_received_pairs')
            ->setCustomRepositoryClass(ReceivedPairRepository::class)
            ->addId();

        $builder->createManyToOne('webhook', 'Webhook')
            ->inversedBy('receivedPairs')
            ->addJoinColumn('webhook_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->addNullableField('receivedField', Type::TEXT, 'received_field');
        $builder->addNullableField('subjectField', Type::TEXT, 'subject_field');
    }

    /**
     * @return string
     */
    public function getReceivedField(): ?string
    {
        return $this->receivedField;
    }

    /**
     * @param string $receivedField
     */
    public function setReceivedField(string $receivedField): void
    {
        $this->receivedField = $receivedField;
    }

    /**
     * @return string
     */
    public function getSubjectField(): ?string
    {
        return $this->subjectField;
    }

    /**
     * @param string $subjectField
     */
    public function setSubjectField(string $subjectField): void
    {
        $this->subjectField = $subjectField;
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


//    public function getId(): int
//    {
//        return $this->id;
//    }
//
//
//    public function setId($id): void
//    {
//        $this->id = $id;
//    }

}