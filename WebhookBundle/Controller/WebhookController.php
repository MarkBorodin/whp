<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\WebhookBundle\Entity\Header;
use Mautic\WebhookBundle\Entity\HeaderRepository;
use Mautic\WebhookBundle\Entity\ReceivedPair;
use Mautic\WebhookBundle\Entity\ReceivedPairRepository;
use Mautic\WebhookBundle\utils\IfPremium;

/**
 * Class WebhookController.
 */
class WebhookController extends FormController
{
    public function __construct()
    {
        $this->setStandardParameters(
            'webhook.webhook', // model name
            'webhook:webhooks', // permission base
            'mautic_webhook', // route base
            'mautic_webhook', // session base
            'mautic.webhook', // lang string base
            'MauticWebhookBundle:Webhook', // template base
            'mautic_webhook', // activeLink
            'mauticWebhook' // mauticContent
        );
    }

    /**
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction($page = 1)
    {
        return parent::indexStandard($page);
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newAction()
    {
        return parent::newStandard();
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction($objectId, $ignorePost = false)
    {
        $GLOBALS['factory'] = $this->factory;

        $isPremium = new IfPremium($this->factory);
        if ($isPremium->checkIfPremium()){
            if (isset($_POST['webhook'])) {
                $webhook = $_POST['webhook'];

                $receivedPairs = $webhook['receivedPairs'];
                foreach ($receivedPairs as $receivedPair){
                    if (isset($receivedPair['id']) and $receivedPair['id'] != ''){
                        /** @var ReceivedPairRepository $repository */
                        $em = $this->factory->getEntityManager();
                        $repository = $em->getRepository(ReceivedPair::class);
                        $entity = $repository->getEntity($receivedPair['id']);
                        /** @var ReceivedPair $entity */
                        $entity->setReceivedField($receivedPair['receivedField']);
                        $entity->setSubjectField($receivedPair['subjectField']);
                        $em->persist($entity);
                        $em->flush();
                        unset($receivedPair['id']);
                    }
                }

                $headers = $webhook['headers'];
                foreach ($headers as $header){
                    if (isset($header['id']) and $header['id'] != ''){
                        /** @var HeaderRepository $repository */
                        $em = $this->factory->getEntityManager();
                        $repository = $em->getRepository(Header::class);
                        $entity = $repository->getEntity($header['id']);
                        /** @var Header $entity */
                        $entity->setHeaderKey($header['headerKey']);
                        $entity->setHeaderValue($header['headerValue']);
                        $em->persist($entity);
                        $em->flush();
                        unset($header['id']);
                    }
                }
            }
        }




        return parent::editStandard($objectId, $ignorePost);
    }

    /**
     * Displays details on a Focus.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewAction($objectId)
    {
        return parent::viewStandard($objectId, 'webhook', 'webhook');
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cloneAction($objectId)
    {
        return parent::cloneStandard($objectId);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        return parent::deleteStandard($objectId);
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        return parent::batchDeleteStandard();
    }
}
