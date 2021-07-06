<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if($this->getUser())
        {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email'))
        {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            
            //Si l'utilisateur existe enregistrer la demande en bd
            if($user)
            {   
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setToken(uniqid());
                $resetPassword->setCreatedAt(new DateTime());
                
                $this->entityManager->persist($resetPassword);
                $this->entityManager->flush();

                //Envoyer un email à l'utilisateur
                $url = $this->generateUrl('update_password', [
                    'token' => $resetPassword->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstName()."<br/> Vous avez demander de réinitialiser votre mot de passe sur le site Ecommerce <br/><br/>";
                $content .= "Veuillez cliquer sur le lien pour <a href='".$url."'>mettre à jour votre mot de passe </a>.";

                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstName().' '.$user->getLastName(), 'Réinitialiser le mot de passe sur Ecommerce', $content);

                $this->addFlash('notice', "Vous allez recevoir dans quelques secondes un email pour réinitialiser votre mot de passe.");
            }
            else {
                $this->addFlash('notice', "Cette adresse email n'est associée à aucun compte !");
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

     /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request,$token, UserPasswordHasherInterface $encoder)
    {
        $resetPassword = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        //Si le token n'existe pas alors redirigé vers modifier mot de passe
        if(!$resetPassword)
        {
            return $this->redirectToRoute('reset_password');

        }
        else {

            $now = new DateTime();
            //Vérifier si la date est toujours valide
            if($now > $resetPassword->getCreatedAt()->modify('+ 2 hour'))
            {
                $this->addFlash('notice', 'Votre demande de mot de passe a expirée. Merci de la renouveller.');
                return $this->redirectToRoute('reset_password');
            }

            //Créer une vue avec mdp et confirmez mdp
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {   
                $new_pwd = $form->get('new_password')->getData();
                

                //Encodage des mdp
                $password = $encoder->hashPassword($resetPassword->getUser(), $new_pwd);
                $resetPassword->getUser()->setPassword($password);

                //Flush en bd
                $this->entityManager->flush();

                $this->addFlash('notice', "Votre mot de passe à bien été mis à jour !");
                return $this->redirectToRoute('app_login');
            }
            
            return $this->render('reset_password/update.html.twig', [
                'form' => $form->createView()
            ]);
            dd($resetPassword);
        }
    }
}
