<?php

declare(strict_types=1);

namespace App\Form\Employee;

use App\Service\CommandeStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeCommandeStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var string|null $currentStatut */
        $currentStatut = $options['current_statut'];

        $choices = array_filter(
            CommandeStatus::employeeUpdatableStatusChoices(),
            static fn (string $status): bool => $status !== CommandeStatus::ANNULEE,
        );

        if (is_string($currentStatut) && $currentStatut !== '' && !in_array($currentStatut, $choices, true)) {
            $choices = [CommandeStatus::label($currentStatut) => $currentStatut] + $choices;
        }

        $builder->add('statut', ChoiceType::class, [
            'label' => 'Nouveau statut',
            'choices' => $choices,
            'expanded' => true,
            'multiple' => false,
            'attr' => ['class' => 'vg-status-list'],
            'choice_attr' => static function (?string $choice, string $key, string $value) use ($currentStatut): array {
                $attrs = [
                    'data-label' => CommandeStatus::label($value),
                    'data-badge' => CommandeStatus::badgeClass($value),
                ];

                if ($value === $currentStatut) {
                    $attrs['data-current'] = 'true';
                }

                return $attrs;
            },
            'choice_label' => static function (?string $choice, string $key, string $value) use ($currentStatut): string {
                $label = CommandeStatus::label($value);

                return $value === $currentStatut ? $label.' (actuel)' : $label;
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => false,
            'current_statut' => null,
        ]);

        $resolver->setAllowedTypes('current_statut', ['string', 'null']);
    }
}
