<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class changePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('confirm', PasswordType::class,['label' => 'Confirmation', 'attr' => ['placeholder' => 'Confirmation']])
        ->add('password', PasswordType::class,['label' => 'Nouveau mot de passe ', 'attr' => ['placeholder' => 'Nouveau mot de passe ']])
        ->add('test', PasswordType::class,['label' => 'Mot de passe actuel', 'attr' => ['placeholder' => 'Mot de passe actuel']])// verication mot de passe au changement
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
