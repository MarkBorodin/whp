<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Form\Type;

use Doctrine\Common\Collections\Criteria;
use Mautic\CategoryBundle\Form\Type\CategoryListType;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\Type\BooleanType;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\CoreBundle\Helper\EncryptionHelper;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\WebhookBundle\Entity\ReceivedPair;
use Mautic\WebhookBundle\Entity\Webhook;
use Mautic\WebhookBundle\Form\DataTransformer\EventsToArrayTransformer;
use Mautic\WebhookBundle\utils\IfPremium;
use MauticPlugin\WHPBundle\Integration\WHPIntegration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookType extends AbstractType
{

    public $factory;

    public function __construct($factory)
    {
        $this->factory   = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'strict_html']));

        /** @var Webhook $webhook */
        $webhook = $builder->getData();

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'    => 'mautic.webhook.form.description',
                'required' => false,
                'attr'     => [
                    'class' => 'form-control',
                ],
            ]
        );

        # CUSTOM
        $isPremium = $GLOBALS['isPremium'];
        if($isPremium) {

            $builder->add(
                'extra',
                BooleanType::class,
                [
                    'label' => 'mautic.webhook.form.extra',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );

            $choices = ['Contact' => 'Contact', 'Company' => 'Company'];
            $builder->add(
                'subject',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $choices,
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'mautic.webhook.form.subject',
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => ['class' => ''],
                ]
            );

            $choices = ['GET' => 'GET', 'POST' => 'POST'];
            $builder->add(
                'method',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $choices,
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'mautic.webhook.form.method',
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => ['class' => ''],
                ]
            );


//            $builder->add(
//                'headers',
//                TextareaType::class,
//                [
//                    'label' => 'mautic.webhook.form.headers',
//                    'required' => false,
//                    'attr' => [
//                        'class' => 'form-control',
//                    ],
//                ]
//            );

            $choices = ['Other' => 'Other', 'Basic' => 'Basic', 'Token' => 'Token'];
            $builder->add(
                'authType',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $choices,
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'mautic.webhook.form.authType',
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => ['class' => ''],
                ]
            );

            $builder->add(
                'login',
                TextareaType::class,
                [
                    'label' => 'mautic.webhook.form.login',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );

            $builder->add(
                'password',
                TextareaType::class,
                [
                    'label' => 'mautic.webhook.form.password',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );

            $builder->add(
                'token',
                TextareaType::class,
                [
                    'label' => 'mautic.webhook.form.token',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );

//            $builder->add(
//                'actualLoad',
//                TextareaType::class,
//                [
//                    'label' => 'mautic.webhook.form.actualLoad',
//                    'required' => false,
//                    'attr' => [
//                        'class' => 'form-control',
//                    ],
//                ]
//            );

//            $builder->add(
//                'fieldsWithValues',
//                TextareaType::class,
//                [
//                    'label' => 'mautic.webhook.form.fieldsWithValues',
//                    'required' => false,
//                    'attr' => [
//                        'class' => 'form-control',
//                    ],
//                ]
//            );

            $builder->add(
                'testContactId',
                TextareaType::class,
                [
                    'label' => 'mautic.webhook.form.testContactId',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );

            //CUSTOM
            $builder->add(
                'sendTestPrem',
                ButtonType::class,
                [
                    'attr'  => ['class' => 'btn btn-success', 'onclick' => 'Mautic.sendHookTestPrem(this)'],
                    'label' => 'mautic.webhook.send.test.payload.prem',
                ]
            );

//            $builder->add(
//                'addField',
//                ButtonType::class,
//                [
//                    'attr'  => ['class' => 'btn btn-success', 'onclick' => 'addItem(this)'],
//                    'label' => 'addField',
//                ]
//            );

            $builder->add('receivedPairs', CollectionType::class, [
                'entry_type'   => ReceivedPairType::class,
                'allow_add' => true,
                'required' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => 'mautic.webhook.form.fieldsWithValues_new',
                ]);


            $builder->add('headers', CollectionType::class, [
                'entry_type'   => HeaderType::class,
                'allow_add' => true,
                'required' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => 'mautic.webhook.form.headers_new',
            ]);

            //CUSTOM
        }
        # CUSTOM

        $builder->add(
            'webhookUrl',
            UrlType::class,
            [
                'label'      => 'mautic.webhook.form.webhook_url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'mautic.webhook.form.secret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.webhook.secret.tooltip',
                ],
                'data'     => $webhook->getSecret() ?? EncryptionHelper::generateKey(),
                'required' => false,
            ]
        );

        $events = $options['events'];

        $choices = [];
        foreach ($events as $type => $event) {
            $choices[$event['label']] = $type;
        }

        $builder->add(
            'events',
            ChoiceType::class,
            [
                'choices'    => $choices,
                'multiple'   => true,
                'expanded'   => true,
                'label'      => 'mautic.webhook.form.webhook.events',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => ''],
                ]
        );

        $builder->get('events')->addModelTransformer(new EventsToArrayTransformer($options['data']));

        $builder->add('buttons', FormButtonsType::class);

        $builder->add(
            'sendTest',
            ButtonType::class,
            [
                'attr'  => ['class' => 'btn btn-success', 'onclick' => 'Mautic.sendHookTest(this)'],
                'label' => 'mautic.webhook.send.test.payload',
            ]
        );

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'Webhook',
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add(
            'eventsOrderbyDir',
            ChoiceType::class,
            [
                'choices' => [
                    'mautic.core.form.default'                                  => '',
                    'mautic.webhook.config.event.orderby.chronological'         => Criteria::ASC,
                    'mautic.webhook.config.event.orderby.reverse.chronological' => Criteria::DESC,
                ],
                'label' => 'mautic.webhook.config.event.orderby',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.webhook.config.event.orderby.tooltip',
                ],
                'placeholder' => '',
                'required'    => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Webhook::class,
            ]
        );

        $resolver->setDefined(['events']);
    }

    public function getBlockPrefix()
    {
        return 'webhook';
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
