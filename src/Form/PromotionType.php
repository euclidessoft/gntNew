<?php

namespace App\Form;

use App\Entity\Promotion;
use App\Form\Type\promoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation')
            ->add('debut', DateType::class, 
            array( 'widget' => 'single_text', 
                    'attr' => ['title' => 'Date de début'],))
            ->add('fin', DateType::class, 
            array( 'widget' => 'single_text', 
                    'attr' => ['title' => 'Date de fin'],))
            ->add('type', promoType::class, ['placeholder' => 'types de promotion *',
                'label' => false,] )
            ->add('premier'  )
            ->add('ugpremier' )
            ->add('deuxieme'  )
            ->add('ugdeuxieme' )
            ->add('troisieme'  )
            ->add('ugtroisieme' )
            ->add('quatrieme'  )
            ->add('ugquatrieme' )
            ->add('cinquieme'  )
            ->add('ugcinquieme' )
            ->add('reduction' )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
}
