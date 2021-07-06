<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {   
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->addFlash('notice', "Merci de nous avoir contacter. Notre équipe vous répondra dans les plus bref délais !");

            $mail = new Mail();

            $firstName = $form->get('prenom')->getData();
            $lastName = $form->get('nom')->getData();
            $email = $form->get('email')->getData();
            $message = $form->get('content')->getData();

            $content = "Bonjour,<br/> ";
            $content .= "Vous avez un message de la part de ". $firstName." ".$lastName."<br/>";
            $content .= "Son adresse email : ".$email."<br/><br/>";
            $content .= "Message : <br/> <br/>".$message."<br/>";

            $mail->send("pouroucheric@gmail.com", "Boutique Ecommerce", 'Message de ', $content);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
