<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Joomla\Http\Http;
use Mautic\CoreBundle\Helper\AbstractFormFieldHelper;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\TokenHelper;
use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\WebhookBundle\Event\WebhookRequestEvent;
use Mautic\WebhookBundle\utils\PremiumFunctionality;
use Mautic\WebhookBundle\WebhookEvents;
use MauticPlugin\MauticTwigTemplatesBundle\Entity\TwigTemplates;
use MauticPlugin\MauticTwigTemplatesBundle\Integration\TwigTemplatesIntegration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CampaignHelper
{
    /**
     * @var Http
     */
    protected $connector;

    /**
     * @var CompanyModel
     */
    protected $companyModel;

    /**
     * Cached contact values in format [contact_id => [key1 => val1, key2 => val1]].
     *
     * @var array
     */
    private $contactsValues = [];

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public $factory;

    public function __construct(Http $connector, $companyModel, EventDispatcherInterface $dispatcher, $factory)
    {
        $this->connector    = $connector;
        $this->companyModel = $companyModel;
        $this->dispatcher   = $dispatcher;
        $this->factory   = $factory;
    }

    /**
     * Prepares the neccessary data transformations and then makes the HTTP request.
     */
    public function fireWebhook(array $config, Lead $contact)
    {
        $payload = $this->getPayload($config, $contact);
        $headers = $this->getHeaders($config, $contact);
        // custom
        $headers = $this->checkAndEncodeCreds($headers);
        $receivedData = $this->getReceivedData($config, $contact);
        // custom
        $url     = rawurldecode(TokenHelper::findLeadTokens($config['url'], $this->getContactValues($contact), true));

        $webhookRequestEvent = new WebhookRequestEvent($contact, $url, $headers, $payload);
        $this->dispatcher->dispatch(WebhookEvents::WEBHOOK_ON_REQUEST, $webhookRequestEvent);

        $response = $this->makeRequest(
            $webhookRequestEvent->getUrl(),
            $config['method'],
            $config['timeout'],
            $webhookRequestEvent->getHeaders(),
            $webhookRequestEvent->getPayload()
        );

        $premiumFunctionality = new PremiumFunctionality($this->factory);
        $result = $premiumFunctionality->processResponse($response, $receivedData);
        if(isset($result)){
            $premiumFunctionality->writeToDB($result, $contact->getId());
        }
    }

    // TODO
    public function checkIfTwig($payload)
    {
        $integrationHelper = $this->factory->getHelper('integration');
        /** @var TwigTemplatesIntegration $twigIntegration */
        $twigIntegration = $integrationHelper->getIntegrationObject(TwigTemplatesIntegration::INTEGRATION_NAME);
        $isPublished = false;
        if($twigIntegration) {
            try {
                $integration = $twigIntegration->getIntegrationSettings();
                $isPublished = $integration->getIsPublished() ?: false;
            } catch (IntegrationNotFoundException $e) {
                return false;
            }

            if($isPublished){
                foreach ($payload as $itemKey => $itemValue){
                    if(strpos($itemValue, 'twig') !== false){
                        $twigArray = explode("=", $itemValue);
                        $twigID = str_replace('}', '', $twigArray[1]);
                        $twigID = trim($twigID);
                        $repository = $this->factory->getEntityManager()->getRepository(TwigTemplates::class);
                        /** @var TwigTemplates $twigEntity */
                        $twigEntity = $repository->getEntity($twigID);
                    }
                }
            }

        }
        return false;
    }
    // TODO

    public function checkAndEncodeCreds($headers)
    {
        if (isset($headers['Authorization'])){
            $authHeader = $headers['Authorization'];
            if(strpos($authHeader, 'Basic') === 0)
            {
                $auth_array = explode(" ", $authHeader);
                $un_pw = base64_encode($auth_array[1]);
                $headers['Authorization'] = 'Basic '.$un_pw;
            }
        }
        return $headers;
    }


    /**
     * Gets the payload fields from the config and if there are tokens it translates them to contact values.
     *
     * @return array
     */
    private function getPayload(array $config, Lead $contact)
    {
        $payload = !empty($config['additional_data']['list']) ? $config['additional_data']['list'] : '';
        $payload = array_flip(AbstractFormFieldHelper::parseList($payload));

        return $this->getTokenValues($payload, $contact);
    }

    /**
     * Gets the payload fields from the config and if there are tokens it translates them to contact values.
     *
     * @return array
     */
    private function getHeaders(array $config, Lead $contact)
    {
        $headers = !empty($config['headers']['list']) ? $config['headers']['list'] : '';
        $headers = array_flip(AbstractFormFieldHelper::parseList($headers));

        return $this->getTokenValues($headers, $contact);
    }

    // custom
    /**
     * Gets the Received Data fields from the config and if there are tokens it translates them to contact values.
     *
     * @return array
     */
    private function getReceivedData(array $config, Lead $contact)
    {
        $receivedData = !empty($config['received_data']['list']) ? $config['received_data']['list'] : '';
        $receivedData = array_flip(AbstractFormFieldHelper::parseList($receivedData));

        return $this->getTokenValues($receivedData, $contact);
    }
    // custom

    /**
     * @param string $url
     * @param string $method
     * @param int    $timeout
     *
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     */
    private function makeRequest($url, $method, $timeout, array $headers, array $payload)
    {
        switch ($method) {
            case 'get':
                $payload  = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').http_build_query($payload);
                $response = $this->connector->get($payload, $headers, $timeout);
                break;
            case 'post':
            case 'put':
            case 'patch':
                $headers = array_change_key_case($headers);
                if (array_key_exists('content-type', $headers) && 'application/json' == strtolower($headers['content-type'])) {
                    $payload                 = json_encode($payload);
                }
                $response = $this->connector->$method($url, $payload, $headers, $timeout);
                break;
            case 'delete':
                $response = $this->connector->delete($url, $headers, $timeout, $payload);
                break;
            default:
                throw new \InvalidArgumentException('HTTP method "'.$method.' is not supported."');
        }

        if (!in_array($response->code, [200, 201])) {
            throw new \OutOfRangeException('Campaign webhook response returned error code: '.$response->code);
        }

        return $response;
    }

    /**
     * Translates tokens to values.
     *
     * @return array
     */
    private function getTokenValues(array $rawTokens, Lead $contact)
    {
        $values        = [];
        $contactValues = $this->getContactValues($contact);

        foreach ($rawTokens as $key => $value) {
            $values[$key] = rawurldecode(TokenHelper::findLeadTokens($value, $contactValues, true));
        }

        return $values;
    }

    /**
     * Gets array of contact values.
     *
     * @return array
     */
    private function getContactValues(Lead $contact)
    {
        if (empty($this->contactsValues[$contact->getId()])) {
            $this->contactsValues[$contact->getId()]              = $contact->getProfileFields();
            $this->contactsValues[$contact->getId()]['ipAddress'] = $this->ipAddressesToCsv($contact->getIpAddresses());
            $this->contactsValues[$contact->getId()]['companies'] = $this->companyModel->getRepository()->getCompaniesByLeadId($contact->getId());
        }

        return $this->contactsValues[$contact->getId()];
    }

    /**
     * @return string
     */
    private function ipAddressesToCsv(Collection $ipAddresses)
    {
        $addresses = [];
        foreach ($ipAddresses as $ipAddress) {
            $addresses[] = $ipAddress->getIpAddress();
        }

        return implode(',', $addresses);
    }
}
