<?php

declare(strict_types=1);

namespace App\Form\Employee;

use App\Entity\MenuImage;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

final class MenuImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filename', FileUploadType::class, [
                'label' => false,
                'upload_dir' => 'public/uploads/menus/',
                'upload_filename' => '[uuid].[extension]',
                'attr' => ['class' => 'vg-menu-image-upload-input'],
                'row_attr' => ['class' => 'vg-menu-image-upload'],
                'file_constraints' => [
                    new Image(maxSize: '5M'),
                ],
            ])
            ->add('position', HiddenType::class, [
                'required' => false,
                'empty_data' => '0',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuImage::class,
            'translation_domain' => false,
        ]);
    }
}
