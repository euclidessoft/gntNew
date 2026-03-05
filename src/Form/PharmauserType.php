<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PharmauserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $id = $options['attr']['id'];
        $builder
//            ->add('ref')
//            ->add('date')
//            ->add('Montant')
//            ->add('versement')
//            ->add('reduction')
//            ->add('suivi')
//            ->add('livraison')
//            ->add('datelivrer')
//            ->add('tva')
//            ->add('credit')
            ->add('user', EntityType::class,
                array( 'class' => User::class,
                    'choice_label' => function($user){
                    return $user->getNom(). ' ' .$user->getPrenom();
                    },
                    'multiple' => false,
                    'query_builder' => function(UserRepository $repository) use ($id) { return $repository->pharmauser($id); },
                    'placeholder' => 'Selectionnez Client *',
                    'label' => false))
//            ->add('livreur')
//            ->add('paiement')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
