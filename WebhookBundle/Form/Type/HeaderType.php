<?php

namespace Mautic\WebhookBundle\Form\Type;

use Mautic\WebhookBundle\Entity\Header;
use Mautic\WebhookBundle\Entity\ReceivedPair;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HeaderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', HiddenType::class);
        $builder->add('headerKey');
        $builder->add('headerValue');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Header::class,
        ]);
    }
}