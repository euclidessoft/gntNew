<?php

namespace App\Form;

use App\Entity\Fournisseur;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference')//code cip
            ->add('alternatif')// code cip alternatif
            // ->add('interne')// code interne
            ->add('desigantion')
            ->add('colisage')
            ->add('dci')
            ->add('prix')
            ->add('prixpublic')
            ->add('mincommande')
            ->add('fabriquant')
            ->add('telephone')
            ->add('adresse')
            ->add('description')
            ->add('tva')
            ->add('pght')
            ->add('fournisseurs', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'designation',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
