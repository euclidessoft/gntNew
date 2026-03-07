<?php

namespace App\Controller\Security;

use App\Entity\Album;
use App\Entity\Candidature;
use App\Entity\Employe;
use App\Entity\Panier;
use App\Form\CandidatureType;
use App\Form\EmployeType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Form\RegistrationType;
//use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\UserType;
use App\Form\changePasswordType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;


#[Route("/{_locale}") ]
class securityController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }


    #[Route("/registration", name :"security_register") ]
    public function new(Request $request, /*UserPasswordHasherInterface*/UserPasswordHasherInterface  $encoder, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mail)
    {
        // if ($this->security->isGranted('ROLE_ADMIN')) {
//            $manager = $this->getDoctrine()->getManager();
            $employe = new Employe();
                $form = $this->createForm(EmployeType::class, $employe);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $hashpass = $encoder->hashPassword($employe, 'GNTPharma');
                //$hashpass = $encoder->encodePassword($user, $user->getPassword());



//                $hashpass = $encoder->encodePassword($user, 'Passer2023');
                $hashpass = $encoder->hashPassword($employe, $employe->getPassword());
                

                //$password = $user->getPassword();
                $employe->setPassword($hashpass);
                $employe->setusername($employe->getNom());
                //                $user->setRoles(['ROLE_CLIENT']);
                $employe->setRoles(['ROLE_ADMIN']);
                // envoie mail
                $token = $tokenGenerator->generateToken();
                $employe->setResetToken($token);
                $this->entityManager->persist($employe);
                $this->entityManager->flush();

                       //Creation compte client
//                       $compte = '411' . str_pad(count($employe->getId()) + 1, 4, '0', STR_PAD_LEFT);
//                       $employe->setCompte($compte);
//                       $manager->persist($employe);
//                       $manager->flush();
                $url = $this->generateUrl('security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $message = (new \Swift_Message('Activation compte utilisateur'))
                    ->setFrom('support@gntpharma-cameroun.com')
                    ->setTo($employe->getEmail())
                    ->setBody($this->renderView('security/security/mail/active.html.twig', ['url' => $url]), 'text/html');
                //                    ->setBody("Cliquez su->setBody($this->renderView('security/security/mail/active.html.twig', [ 'url' => $url ]), 'text/html');r le lien suivant pour activer votre compte utilisasateur " . $url, 'text/html');
                //                $message = (new \Swift_Message('Activation compte utilisateur'))
                //                 ->setFrom('support@gntpharma-cameroun.com')
                //                 ->setTo($user->getEmail())
                //                 ->setBody($this->renderView('licence/facture.html.twig'), 'text/html');

                $mail->send($message);
                // fin envoie mail

                $this->addFlash('notice', 'Utilisateur créé, un message a été envoyé à son adresse mail pour l\'activation du compte');
                return $this->redirectToRoute('security_login');
            }

            $response = $this->render('security/security/index.html.twig', [
                'form' => $form->createView(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        // } else {
        //     $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
        //     return $this->redirectToRoute('security_login');
        // }
    }

    // #[Route("/login", name :"security_login") ]
    // public function login(AuthenticationUtils $auth)
    // {
    //     $error = $auth->getLastAuthenticationError();
    //     $last_user = $auth->getLastUsername();

    //     $response = $this->render('security/security/login.html.twig', [
    //         'error' => $error,
    //         'last_user' => $last_user,
    //     ]);
    //     $response->setSharedMaxAge(0);
    //     $response->headers->addCacheControlDirective('no-cache', true);
    //     $response->headers->addCacheControlDirective('no-store', true);
    //     $response->headers->addCacheControlDirective('must-revalidate', true);
    //     $response->setCache([
    //         'max_age' => 0,
    //         'private' => true,
    //     ]);
    //     return $response;
    // }

    // #[Route("/logout", name :"security_logout") ]
    // public function logout() {

    //     $response = $this->security->logout(false);
    //     return $response;
    // }

    #[Route("/profile", name :"security_profile") ]
    public function profile(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
            $response = $this->render('security/security/profile.html.twig', [
                'user' => $this->getUser(),
                'panier' => $panier,
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('security/security/admin/profile.html.twig', [
                'user' => $this->getUser(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_login');
        }
    }

    #[Route("/profile/professionnelles", name :"security_profile_professionnelle") ]
    public function Professionnelles(SessionInterface $session)
    {
//        if ($this->security->isGranted('ROLE_EMPLOYER')) {
//            $panier = $session->get("panier", []);
//            $response = $this->render('security/security/profile.html.twig', [
//                'user' => $this->getUser(),
//                'panier' => $panier,
//            ]);
//            $response->setSharedMaxAge(0);
//            $response->headers->addCacheControlDirective('no-cache', true);
//            $response->headers->addCacheControlDirective('no-store', true);
//            $response->headers->addCacheControlDirective('must-revalidate', true);
//            $response->setCache([
//                'max_age' => 0,
//                'private' => true,
//            ]);
//            return $response;
//        } else
            if ($this->security->isGranted('ROLE_BACK')
                || $this->security->isGranted('ROLE_EMPLOYER')) {

            $response = $this->render('security/security/admin/professionnelle.html.twig', [
                'user' => $this->getUser(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');

            $response = $this->redirectToRoute('security_login');
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        }
    }


    #[Route("/edit_profile", name :"security_profile_edit") ]
    public function edit(SessionInterface $session, Request $request)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            // $panier = $session->get("panier", []);
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
//            $em = $this->getDoctrine()->getManager();
            $user = $this->entityManager->getRepository(User::class)->find($this->getUser()->getId());
            $form = $this->createForm(UserType::class, $user);
            $form->remove('username');
            $form->remove('fonction');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('notice', 'Profil modifié avec succès');
                return $this->redirectToRoute('security_profile', ['id' => $user->getId()]);
            }
            $response = $this->render('security/security/edit.html.twig', [
                'form' => $form->createView(),
                'panier' => $panier,
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } elseif ($this->security->isGranted('ROLE_BACK')) {

//            $em = $this->getDoctrine()->getManager();
            $user = $this->entityManager->getRepository(User::class)->find($this->getUser()->getId());
            $form = $this->createForm(UserType::class, $user);
            $form->remove('username');
            $form->remove('fonction');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('notice', 'Profil modifié avec succès');
                return $this->redirectToRoute('security_profile', ['id' => $user->getId()]);
            }
            if ($this->security->isGranted('ROLE_CLIENT')) {
                $response = $this->render('security/security/edit.html.twig', [
                    'form' => $form->createView(),
                ]);
            } else {
                $response = $this->render('security/security/admin/edit.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_logout');
        }
    }


    #[Route("/ChangePassword", name :"security_change_password") ]
    public function change(SessionInterface $session, Request $request, UserPasswordHasherInterface $encoder)
    {
        if ($this->getUser() !== null) {
            // $panier = $session->get("panier", []);
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
            $userinit = new User();
            $userinit->setPrenom('blabla');
            $userinit->setNom('blabla');
            $userinit->setPhone('770000000');
            $userinit->setEmail('mail@mail.com');
            $userinit->setAdresse('jfjfjffj');
            $form = $this->createForm(changePasswordType::class, $userinit);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

//                $em = $this->getDoctrine()->getManager();
                $user = $this->entityManager->getRepository(User::class)->find($this->getUser()->getId());
                if ($encoder->isPasswordValid($user, $userinit->getTest())) {
                    $newpassword = $encoder->hashPassword($user, $userinit->getPassword());
                    $user->setPassword($newpassword);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $this->addFlash('change', 'Votre mot de passe  a été modifié, veuillez vous reconnecter!');
                    return $this->redirectToRoute('security_login');
                } else {
                    $form->addError(new FormError('Ancien mot de passe incorrecte'));
                }
            }
            if ($this->security->isGranted('ROLE_BACK'))
                $response = $this->render('security/security/admin/changepassword.html.twig', [
                    'form' => $form->createView(),
                    'panier' => $panier,
                ]);
                else
                $response = $this->render('security/security/changepassword.html.twig', [
                    'form' => $form->createView(),
                    'panier' => $panier,
                ]);

            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_login');
        }
    }

    #[Route("/forgottenPassword", name :"security_forgotten_password") ]
    public function forgotten(Request $request, TokenGeneratorInterface $tokenGenerator)
    {
        if ($request->isMethod('POST')) {

            $email = $request->request->get('_mail');

//            $em = $this->getDoctrine()->getManager();
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
            if ($user === null) {
                $this->addFlash('notice', 'Adresse email inconnue');
                return $this->redirectToRoute('security_forgotten_password');
            }
            $token = $tokenGenerator->generateToken();
            $user->setResetToken($token);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $url = $this->generateUrl('security_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

//            $message = (new \Swift_Message('Réinitialisation mot de passe'))
//                ->setFrom('support@gntpharma-cameroun.com')
//                ->setTo($user->getEmail())
//                ->setBody($this->renderView('security/security/mail/forget.html.twig', ['url' => $url]), 'text/html');
//            //                ->setBody("Cliquez sur le lien suivant pour réinitialiser votre mot de passe " . $url, 'text/html');
//
//            $mail->send($message);
//            $this->addFlash('change', 'Un message a été envoyé à votre adresse email, veuillez consulter votre boite de réception');
        }


        $response = $this->render('security/security/forget.html.twig');

        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-cache', true);
        return $response;
    }

    #[Route("/Users", name :"security_users") ]
    public function users(UserRepository $userRepository)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {

            $response = $this->render('security/security/users.html.twig', [
                'users' => $userRepository->findAll(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_logout');
        }
    }

    #[Route("/Clients", name :"security_clients") ]
    public function clients(UserRepository $userRepository)
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('security/security/clients.html.twig', [
                'users' => $userRepository->findBy(['client' => true]),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_logout');
        }
    }

    #[Route("/User/{user}", name :"security_user") ]
    public function user(UserRepository $userRepository, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {

            $response = $this->render('security/security/user.html.twig', [
                'user' => $user,
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_login');
        }
    }

    #[Route("/ResetPassword/{token}", name :"security_reset_password") ]
    public function reset(Request $request, string $token, UserPasswordEncoderInterface $encoder)
    {
        $userinit = new User();
        $userinit->setEmail('chamge@blala.com');
        $userinit->setNom('chamge@blala.com');
        $userinit->setPrenom('jfjfjffj');
        $userinit->setPhone('775423500');
        $userinit->setAdresse('jfjfjffj');
        $form = $this->createForm(changePasswordType::class, $userinit);
        $form->remove('test');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
            $user = $this->entityManager->getRepository(User::class)->findOneByResetToken($token);
            if ($user === null) {
                $this->addFlash('notice', 'Chaine de réinitialisation invalide');
                return $this->redirectToRoute('security_login');
            }
            $user->setResetToken(null);
            $newpassword = $encoder->encodePassword($user, $userinit->getPassword());
            $user->setPassword($newpassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('change', 'Réinitialisation réussie');
            $response = $this->redirectToRoute('security_login');
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        }


        return $this->render(
            'security/security/reset.html.twig',
            ['form' => $form->createView()]
        );
    }

    #[Route("/Activation/{token}", name :"security_activation") ]
    public function active(Request $request, string $token)
    {

//        $em = $this->getDoctrine()->getManager();
        $user = $this->entityManager->getRepository(User::class)->findOneByResetToken($token);
        if ($user === null) {
            $this->addFlash('notice', 'Chaine d\'activation invalide');
            return $this->redirectToRoute('security_login');
        }
        $user->setEnabled(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->addFlash('notice', 'Compte actif, veuillez  définir votre mot de passe pour la première connexion');
        return $this->redirectToRoute('security_reset_password', ['token' => $token]);


        return $this->render(
            'security/security/reset.html.twig',
            ['form' => $form->createView()]
        );
    }

    #[Route("/UserDisable", name :"security_user_disable") ]
    public function UserdisableAction(Request $request)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
//            $em = $this->getDoctrine()->getManager();
            //$users = $em->getrepository(User::class)->findBy(array('agence' => $this->getUser()->getAgence()->getId()));
            $user = $this->entityManager->getrepository(User::class)->find($request->get('usr'));
            if (!$this->security->isGranted('ROLE_ADMIN') || $user->getFonction() != 'proprietaire') {
                $user->setEnabled(false);
                $this->entityManager->persist($user);
            }
            $this->entityManager->flush();
            $res['ok'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        } else return $this->redirect($this->generateUrl('security_login'));
    }

    #[Route("/UserEnable", name :"security_user_enable") ]
    public function UserenableAction(Request $request)
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {
//            $em = $this->getDoctrine()->getManager();
            //$users = $em->getrepository(User::class)->findBy(array('agence' => $this->getUser()->getAgence()->getId()));
            $user = $this->entityManager->getrepository(User::class)->find($request->get('usr'));
            if (!$this->security->isGranted('ROLE_ADMIN') || $user->getFonction() != 'proprietaire') {
                $user->setEnabled(true);
                $this->entityManager->persist($user);
            }
            $this->entityManager->flush();
            $res['ok'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        } else return $this->redirect($this->generateUrl('security_login'));
    }

    #[Route("/edit_user/{user}", name :"security_user_edit") ]
    public function edit_user(Request $request, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {


            $form = $this->createForm(RegistrationType::class, $user);
            $form->remove('username');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $this->entityManager->flush();
                $this->addFlash('notice', 'Modifié avec succès');
                return $this->redirectToRoute('security_user', ['user' => $user->getId()]);
            }
            $response = $this->render('security/security/edit.html.twig', [
                'form' => $form->createView(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_login');
        }
    }


    #[Route("/Admin/registration", name :"security_admin_register") ]
    public function admin_new(Request $request, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mail)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
//            $manager = $this->getDoctrine()->getManager();
            $user = new Employe();
            $form = $this->createForm(RegistrationType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // $hashpass = $encoder->encodePassword($user, 'Moda2020');
                $hashpass = $encoder->encodePassword($user, $user->getPassword());

                //$password = $user->getPassword();
                $user->setPassword($hashpass);
                $user->setUsername($user->getNom());

                //$user->setDatenaiss(date_create_from_format('j/m/Y', $user->getBirthday()));//creation de la date de naissance

                $user->setRoles(['ROLE_ADMIN']);

                switch ($user->getFonction()) {
                    case 'Administrateur': {
                            $user->setRoles(['ROLE_ADMIN']);
                            break;
                        }
                    case 'Client': {
                            $user->setRoles(['ROLE_CLIENT']);
                            $user->setClient(true);
                            break;
                        }
                    case 'Financier': {
                            $user->setRoles(['ROLE_FINANCE']);
                            break;
                        }
                    case 'RH': {
                        $user->setRoles(['ROLE_RH']);
                        break;
                    }
                    case 'EMPLOYER': {
                        $user->setRoles(['ROLE_EMPLOYER']);
                        break;
                    }
                    case 'Gestionnaire de stock': {
                            $user->setRoles(['ROLE_STOCK']);
                            break;
                        }
                    case 'Livreur': {
                            $user->setRoles(['ROLE_LIVREUR']);
                            $user->setLivreur(true);
                            break;
                        }
                }
                // envoie mail
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

//                $compte = '411' . str_pad($user->getId(), 4, '0', STR_PAD_LEFT);
//                $user->setCompte($compte);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $url = $this->generateUrl('security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $message = (new \Swift_Message('Activation compte utilisateur'))
                    ->setFrom('support@gntpharma-cameroun.com')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('security/security/mail/active.html.twig', ['url' => $url]), 'text/html');
                //                    ->setBody("Cliquez sur le lien suivant pour activer votre compte utilisasateur " . $url, 'text/html');


                $mail->send($message);
                // fin envoie mail

                $this->addFlash('notice', 'Utilisateur créé, un message a été envoyé à son adresse mail pour l\'activation du compte');

                //return $this->redirectToRoute('security_profile');
                return $this->redirectToRoute('security_admin_register');
            }

            $response = $this->render('security/security/admin/admin_add.html.twig', [
                'form' => $form->createView(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_login');
        }
    }

    #[Route("/delete_user/", name :"security_user_delete") ]
    public function delete_user(Request $request)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {

            try {
//                $em = $this->getDoctrine()->getManager();

                $user = $this->entityManager->getrepository(User::class)->find($request->get('usr'));
                $this->entityManager->remove($user);

                $this->entityManager->flush();
                $this->addFlash('notice', 'Utilisateur suppriméé avec succés');
            } catch (\Exception $exception) {
                $this->addFlash('notice', 'Cet utilisateur ne peut être supprimer pour des raisons de traçabilité');
            }
            $res['ok'] = $this->generateUrl('security_users', [], UrlGeneratorInterface::ABSOLUTE_URL);;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        } else {
            $this->addFlash('notice', 'Vous n\'avez pas le droit d\'acceder à cette partie de l\'application');
            return $this->redirectToRoute('security_logout');
        }
    }
}
