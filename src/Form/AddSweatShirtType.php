<?php

namespace App\Form;

use App\Entity\SweatShirts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AddSweatShirtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('price', NumberType::class)
            ->add('xs_stock', NumberType::class)
            ->add('s_stock', NumberType::class)
            ->add('m_stock', NumberType::class)
            ->add('l_stock', NumberType::class)
            ->add('xl_stock', NumberType::class)
            ->add('img', FileType::class, [
                'mapped' => false,
                'required' => $options['img_required'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPG or PNG image',
                    ])
                ],
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Mettre en avant sur la page d’accueil',
                'required' => false,
                'mapped' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SweatShirts::class,
            'img_required' => true,
        ]);
    }
}

