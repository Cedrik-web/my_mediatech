<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Entrez votre e-mail: ',
                'attr' => [
                    'placeholder' => 'votre email'
                ]
            ])
            ->add('last_name', null, [
                'label' => 'Entrez votre nom: ',
                'attr' => [
                    'placeholder' => 'votre nom'
                ]
            ])
            ->add('first_name', null, [
                'label' => 'Entrez votre prénom: ',
                'attr' => [
                    'placeholder' => 'votre prénom'
                ]
            ])
            ->add('adress', null, [
                'label' => 'Entrez votre adresse: ',
                'attr' => [
                    'placeholder' => 'votre adresse'
                ]
            ])
            ->add('city', null, [
                'label' => 'Entrez votre ville: ',
                'attr' => [
                    'placeholder' => 'votre ville'
                ]
            ])
            ->add('zip_code', null, [
                'label' => 'Entrez le code postal de votre ville: ',
                'attr' => [
                    'placeholder' => 'le code postal de votre ville'
                ]
            ])
            ->add('country', null, [
                'label' => 'Entrez le pays: ',
                'attr' => [
                    'placeholder' => 'votre pays'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Entrez votre mot de passe: ',
                    'attr' => [
                        'placeholder' => '********'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmez votre mot de passe: ',
                    'attr' => [
                        'placeholder' => '********'
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
