<?php

declare(strict_types=1);

namespace App\Form\Employee;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CancelCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contactMode', ChoiceType::class, [
                'label' => 'Mode de contact',
                'choices' => [
                    'Appel GSM' => 'gsm',
                    'Email' => 'email',
                ],
                'placeholder' => 'Sélectionner un mode',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('employeeActionReason', TextareaType::class, [
                'label' => 'Motif',
                'attr' => ['rows' => 4, 'placeholder' => 'Décrivez brièvement la raison de l\'annulation…'],
            ])
            ->add('contactedAt', DateTimeType::class, [
                'label' => 'Date de contact',
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => false,
        ]);
    }
}
