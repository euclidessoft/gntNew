<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Type\SexeType;
use App\Entity\Departement;
use App\Entity\Employe;
use App\Entity\Poste;
use App\Form\Type\BloodGroupType;
use App\Form\Type\LinkType;
use App\Form\Type\CategorieType;
use App\Form\Type\EchelleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricule')
            ->add('prenom')
            ->add('nom')
            ->add('nationalite')
            ->add('email')
            ->add('phone')
            ->add('adresse')
            ->add('poste', EntityType::class, [
                'class' => Poste::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un poste',
                'required' => true,
            ])
            ->add('Date_naissance', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('lieu_naissance')
            ->add('nationalite')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'placeholder' => 'Choisissez le sexe',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'label' => 'Sexe',
            ])
            ->add('enfant')
            ->add('civilite', ChoiceType::class, [
                'choices' => [
                    'Célibataire' => 'celibataire',
                    'Marié(e)' => 'marie',
                    'Divorcé(e)' => 'divorce',
                    'Veuf(ve)' => 'veuf',
                ],
                'placeholder' => 'Situation matrimoniale',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'label' => 'situation',
            ])
            ->add('hiredate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('fonction', ChoiceType::class, [
                'choices' => User::jobs,
                'placeholder' => 'Niveau d\'accès *',
                'label' => false,
                'required' => true
            ])
              ->add('niveau2', ChoiceType::class, [
            'choices' => User::jobsimple,
            'placeholder' => 'types d\'utilisateur *',
            'label' => false,
            'required' => true
        ])
            ->add('password', PasswordType::class, ['label' => 'Mot de password'])
            ->add('bloodgroup', BloodGroupType::class,array('placeholder' => 'Groupe sanguin'))
            ->add('diabete', CheckboxType::class, [
                'label' => 'diabete',
                'required' => false,
            ])
            ->add('handicap', CheckboxType::class, [
                'label' => 'Handicap',
                'required' => false,
            ])
            ->add('hypo', CheckboxType::class, [
                'label' => 'Hypoglycémie',
                'required' => false,
            ])
            ->add('hyper', CheckboxType::class, [
                'label' => 'Hypertension',
                'required' => false,
            ])
            ->add('epilepsie', CheckboxType::class, [
                'label' => 'epilepsie',
                'required' => false,
            ])
            ->add('remark')
            ->add('famillyname')
            ->add('famillylink',LinkType::class,array('placeholder' => 'Lien de parenté'))
            ->add('famillyphone')
            ->add('categorie',CategorieType::class,array('placeholder' => 'Catégorie'))
            ->add('echelle',EchelleType::class,array('placeholder' => 'Echellon'))
            ->add('cnps')
            ->add('banque')
//            ->add('sursalaire')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}
