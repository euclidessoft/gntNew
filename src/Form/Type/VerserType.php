<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Security;

class VerserType extends AbstractType
{
	 private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

	public function configureOptions(OptionsResolver $resolver)
	{
		  $choices = [
				'Espece' => 'Espece',
			];

			//  Vérification du rôle
			if ($this->security->isGranted('ROLE_FINANCE')) {
				$choices['Cheque'] = 'Cheque';
				$choices['Virement'] = 'Virement';
			}
		$resolver->setDefaults(array(
						'choices' => $choices
						));
	}
	public function getParent()
	{
		return ChoiceType::class;
	}

}