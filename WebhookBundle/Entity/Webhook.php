<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CategoryBundle\Entity\Category;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Webhook extends FormEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var \Mautic\CategoryBundle\Entity\Category
     **/
    private $category;

    /**
     * @var ArrayCollection
     */
    private $events;

    /**
     * @var ArrayCollection
     */
    private $queues;

    /**
     * @var ArrayCollection
     */
    private $logs;

    /**
     * @var array
     */
    private $removedEvents = [];

    /**
     * @var array
     */
    private $payload;

    /**
     * Holds a simplified array of events, just an array of event types.
     * It's used for API serializaiton.
     *
     * @var array
     */
    private $triggers = [];

    /**
     * ASC or DESC order for fetching order of the events when queue mode is on.
     * Null means use the global default.
     *
     * @var string
     */
    private $eventsOrderbyDir;

    # CUSTOM
    /**
     * @ORM\Column(type="text")
     */
    private $extra;

    /**
     * @ORM\Column(type="text")
     */
    private $method;

    /**
     * @ORM\Column(type="text")
     */
    private $authType;

    /**
     * @ORM\Column(type="text")
     */
    private $login;

    /**
     * @ORM\Column(type="text")
     */
    private $password;

    /**
     * @ORM\Column(type="text")
     */
    private $token;

    /**
     * @ORM\Column(type="text")
     */
    private $actualLoad;

    /**
     * @ORM\Column(type="text")
     */
    private $fieldsWithValues;

    /**
     * @ORM\Column(type="text")
     */
    private $testContactId;

    /**
     * @ORM\Column(type="text")
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $headers;
    # CUSTOM

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->queues = new ArrayCollection();
        $this->logs   = new ArrayCollection();
    }

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('webhooks')
            ->setCustomRepositoryClass(WebhookRepository::class);

        $builder->addIdColumns();

        $builder->addCategory();

        $builder->createOneToMany('events', 'Event')
            ->orphanRemoval()
            ->setIndexBy('event_type')
            ->mappedBy('webhook')
            ->cascadePersist()
            ->cascadeMerge()
            ->cascadeDetach()
            ->build();

        $builder->createOneToMany('queues', 'WebhookQueue')
            ->mappedBy('webhook')
            ->fetchExtraLazy()
            ->cascadePersist()
            ->cascadeMerge()
            ->cascadeDetach()
            ->build();

        $builder->createOneToMany('logs', 'Log')->setOrderBy(['dateAdded' => Criteria::DESC])
            ->fetchExtraLazy()
            ->mappedBy('webhook')
            ->cascadePersist()
            ->cascadeMerge()
            ->cascadeDetach()
            ->build();

        $builder->addNamedField('webhookUrl', Types::STRING, 'webhook_url');
        $builder->addField('secret', Types::STRING);
        $builder->addNullableField('eventsOrderbyDir', Types::STRING, 'events_orderby_dir');

        # CUSTOM
        $builder->addNullableField('extra', Types::BOOLEAN);
        $builder->addNullableField('method', Types::STRING);
        $builder->addNullableField('authType', Types::STRING, 'auth_type');
        $builder->addNullableField('login', Types::STRING);
        $builder->addNullableField('password', Types::TEXT);
        $builder->addNullableField('token', Types::TEXT);
        $builder->addNullableField('actualLoad', Types::TEXT, 'actual_load');
        $builder->addNullableField('fieldsWithValues', Types::TEXT, 'fields_with_values');
        $builder->addNullableField('testContactId', Types::STRING, 'test_contact_id');
        $builder->addNullableField('subject', Types::STRING);
        $builder->addNullableField('headers', Types::TEXT);
        # CUSTOM
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('hook')
            ->addListProperties(
                [
                    'id',
                    'name',
                    'description',
                    'webhookUrl',
                    'secret',
                    'eventsOrderbyDir',
                    'category',
                    'triggers',
                    'extra',
                    'method',
                    'authType',
                    'login',
                    'password',
                    'token',
                    'actualLoad',
                    'fieldsWithValues',
                    'testContactId',
                    'subject',
                    'headers',
                ]
            )
            ->build();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint(
            'name',
            new NotBlank(
                [
                    'message' => 'mautic.core.name.required',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'webhookUrl',
            new Assert\Url(
                [
                    'message' => 'mautic.core.valid_url_required',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'webhookUrl',
            new Assert\NotBlank(
                [
                    'message' => 'mautic.core.valid_url_required',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'eventsOrderbyDir',
            new Assert\Choice(
                [
                    null,
                    Criteria::ASC,
                    Criteria::DESC,
                ]
            )
        );
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Webhook
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description
     *
     * @return Webhook
     */
    public function setDescription($description)
    {
        $this->isChanged('description', $description);
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $webhookUrl
     *
     * @return Webhook
     */
    public function setWebhookUrl($webhookUrl)
    {
        $this->isChanged('webhookUrl', $webhookUrl);
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebhookUrl()
    {
        return $this->webhookUrl;
    }

    /**
     * @param string $secret
     *
     * @return Webhook
     */
    public function setSecret($secret)
    {
        $this->isChanged('secret', $secret);
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return Webhook
     */
    public function setCategory(Category $category = null)
    {
        $this->isChanged('category', $category);
        $this->category = $category;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param $events
     *
     * @return $this
     */
    public function setEvents($events)
    {
        $this->isChanged('events', $events);

        $this->events = $events;
        /** @var \Mautic\WebhookBundle\Entity\Event $event */
        foreach ($events as $event) {
            $event->setWebhook($this);
        }

        return $this;
    }

    /**
     * This builds a simple array with subscribed events.
     *
     * @return array
     */
    public function buildTriggers()
    {
        foreach ($this->events as $event) {
            $this->triggers[] = $event->getEventType();
        }
    }

    /**
     * Takes the array of triggers and builds events from them if they don't exist already.
     */
    public function setTriggers(array $triggers)
    {
        foreach ($triggers as $key) {
            $this->addTrigger($key);
        }
    }

    /**
     * Takes a trigger (event type) and builds the Event object form it if it doesn't exist already.
     *
     * @param string $key
     *
     * @return bool
     */
    public function addTrigger($key)
    {
        if ($this->eventExists($key)) {
            return false;
        }

        $event = new Event();
        $event->setEventType($key);
        $event->setWebhook($this);
        $this->addEvent($event);

        return true;
    }

    /**
     * Check if an event exists comared to its type.
     *
     * @param string $key
     *
     * @return bool
     */
    public function eventExists($key)
    {
        foreach ($this->events as $event) {
            if ($event->getEventType() === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return $this
     */
    public function addEvent(Event $event)
    {
        $this->isChanged('events', $event);

        $this->events[] = $event;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeEvent(Event $event)
    {
        $this->isChanged('events', $event);
        $this->removedEvents[] = $event;
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * @param string $eventsOrderbyDir
     */
    public function setEventsOrderbyDir($eventsOrderbyDir)
    {
        $this->isChanged('eventsOrderbyDir', $eventsOrderbyDir);
        $this->eventsOrderbyDir = $eventsOrderbyDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventsOrderbyDir()
    {
        return $this->eventsOrderbyDir;
    }

    /**
     * @return ArrayCollection
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * @return $this
     */
    public function addQueues($queues)
    {
        $this->queues = $queues;

        /** @var \Mautic\WebhookBundle\Entity\WebhookQueue $queue */
        foreach ($queues as $queue) {
            $queue->setWebhook($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addQueue(WebhookQueue $queue)
    {
        $this->queues[] = $queue;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeQueue(WebhookQueue $queue)
    {
        $this->queues->removeElement($queue);

        return $this;
    }

    /**
     * Get log entities.
     *
     * @return ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return $this
     */
    public function addLogs($logs)
    {
        $this->logs = $logs;

        /** @var \Mautic\WebhookBundle\Entity\Log $log */
        foreach ($logs as $log) {
            $log->setWebhook($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addLog(Log $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeLog(Log $log)
    {
        $this->logs->removeElement($log);

        return $this;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return Webhook
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function wasModifiedRecently()
    {
        $dateModified = $this->getDateModified();

        if (null === $dateModified) {
            return false;
        }

        $aWhileBack = (new \DateTime())->modify('-2 days');

        if ($dateModified < $aWhileBack) {
            return false;
        }

        return true;
    }

    /**
     * @param string $prop
     */
    protected function isChanged($prop, $val)
    {
        $getter  = 'get'.ucfirst($prop);
        $current = $this->$getter();
        if ('category' == $prop) {
            $currentId = ($current) ? $current->getId() : '';
            $newId     = ($val) ? $val->getId() : null;
            if ($currentId != $newId) {
                $this->changes[$prop] = [$currentId, $newId];
            }
        } elseif ('events' == $prop) {
            $this->changes[$prop] = [];
        } elseif ($current != $val) {
            $this->changes[$prop] = [$current, $val];
        } else {
            parent::isChanged($prop, $val);
        }
    }

    /**
     * @return mixed
     */
    public function getTestContactId()
    {
        return $this->testContactId;
    }

    /**
     * @param mixed $testContactId
     */
    public function setTestContactId($testContactId): void
    {
        $this->isChanged('testContactId', $testContactId);
        $this->testContactId = $testContactId;
    }

    /**
     * @return mixed
     */
    public function getFieldsWithValues()
    {
        return $this->fieldsWithValues;
    }

    /**
     * @param mixed $fieldsWithValues
     */
    public function setFieldsWithValues($fieldsWithValues): void
    {
        $this->isChanged('fieldsWithValues', $fieldsWithValues);
        $this->fieldsWithValues = $fieldsWithValues;
    }

    /**
     * @return mixed
     */
    public function getActualLoad()
    {
        return $this->actualLoad;
    }

    /**
     * @param mixed $load
     */
    public function setActualLoad($actualLoad): void
    {
        $this->isChanged('actualLoad', $actualLoad);
        $this->actualLoad = $actualLoad;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->isChanged('token', $token);
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->isChanged('password', $password);
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login): void
    {
        $this->isChanged('login', $login);
        $this->login = $login;
    }

    /**
     * @return mixed
     */
    public function getAuthType()
    {
        return $this->authType;
    }

    /**
     * @param mixed $authType
     */
    public function setAuthType($authType): void
    {
        $this->isChanged('authType', $authType);
        $this->authType = $authType;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->isChanged('method', $method);
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra): void
    {
        $this->isChanged('extra', $extra);
        $this->extra = $extra;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void
    {
        $this->isChanged('subject', $subject);
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers): void
    {
        $this->isChanged('headers', $headers);
        $this->headers = $headers;
    }
}
