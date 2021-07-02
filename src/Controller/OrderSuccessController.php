<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart ,$stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() !== $this->getUser())
        {
            return $this->redirectToRoute('home');
        }

        // Modifier le statut isPaid de notre commande en mettant 1
        if(!$order->getIsPaid())
        {   
            //Vider la session cart
            $cart->remove();

            //Modifier le statut payer à 1
            $order->setIsPaid(1);
            $this->entityManager->flush();

            //Envoyer une email à notre client pour lui confirmer sa commande

            $mail = new Mail();
            $content = "Bonjour ". $order->getUser()->getFirstName()."<br/> Merci pour votre commande sur le site Ecommerce !<br/>";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstName(), 'Votre commande sur le site Ecommerce !', $content);
        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
