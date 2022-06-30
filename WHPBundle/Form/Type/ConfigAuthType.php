<?php

declare(strict_types=1);

namespace MauticPlugin\WHPBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfigAuthType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        $builder->add(
//          'key',
//          TextType::class,
//          [
//            'label'    => 'rens.form.key',
//            'required' => true,
//            'attr'     => [
//              'class' => 'form-control',
//            ],
//            'constraints' => [
//              new NotBlank(['message' => 'rens.form.key.required']),
//            ],
//          ]
//        );
//
//        $builder->add(
//          'secret',
//          TextType::class,
//          [
//            'label'      => 'rens.form.secret',
//            'label_attr' => ['class' => 'control-label'],
//            'required'   => true,
//            'attr'       => [
//              'class' => 'form-control',
//            ],
//            'constraints' => [
//              new NotBlank(['message' => 'rens.form.secret.required']),
//            ],
//          ]
//        );
//
//        $builder->add(
//            'senderName',
//            TextType::class,
//            [
//                'label'      => 'rens.form.senderName',
//                'label_attr' => ['class' => 'control-label'],
//                'required'   => true,
//                'attr'       => [
//                    'class' => 'form-control',
//                ],
//                'constraints' => [
//                    new NotBlank(['message' => 'rens.form.senderName.required']),
//                ],
//            ]
//        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'integration' => null,
            ]
        );
    }
}
