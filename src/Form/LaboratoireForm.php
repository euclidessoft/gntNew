<?php

namespace App\Form;

use App\Entity\Laboratoire;
use App\Entity\Pharmacie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LaboratoireForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             ->add('email')
            ->add('prenom')
            ->add('nom')
            ->add('phone')
            ->add('adresse')
            ->add('password')
            ->add('boitepostale')
            ->add('siteweb')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Laboratoire::class,
        ]);
    }
}
