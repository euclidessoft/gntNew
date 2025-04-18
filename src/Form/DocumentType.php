<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Employe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filePath',FileType::class,[
                'label' => 'Télécharger les documents',
                'required' => false,
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('fileName')
            ->add('employe',EntityType::class, [
                'class' => Employe::class,
                'choice_label' => function(Employe $employe){
                return $employe->getNom().' '.$employe->getPrenom();
                },
                'placeholder' => 'Selectionnez un employé',
                'required' => 'true',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
