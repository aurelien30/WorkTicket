<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TicketController extends AbstractController{

#[Route('/tickets', name: 'ticket_index')]
#[IsGranted('ROLE_USER')]
public function index(TicketRepository $ticketRepository): Response
{
    //Un technicien ne peut voir tous les tickets, un utilisateur normal ne voit que les siens
    if ($this->isGranted('ROLE_TECHNICIEN')) {
        $tickets = $ticketRepository->findAll();
    } else {
        $tickets = $ticketRepository->findBy(['creator' => $this->getUser()]);
}
return $this->render('ticket/index.html.twig', [
    'tickets' => $tickets,
]);
}

#[Route('/tickets/new', name: 'ticket_new')]
#[IsGranted('ROLE_USER')]
public function new(Request $request, EntityManagerInterface $em): Response{

     $ticket = new Ticket();

     if ($request->isMethod('POST')) {
        $ticket->setTitle($request->request->get('title'));
        $ticket->setDescription($request->request->get('description'));
        $ticket->setPriority($request->request->get('priority'));
        $ticket->setStatus('OUVERT');
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setCreator($this->getUser());

        $em->persist($ticket);
        $em->flush();

        return $this->redirectToRoute('ticket_index');
     }

     return $this->render('ticket/new.html.twig');
   }
}