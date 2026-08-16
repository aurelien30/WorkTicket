<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Comment;
use App\Enum\TicketStatus;
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
  // Un technicien voit tous les tickets, un utilisateur normal ne voit que les siens
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
        $ticket->setStatus(TicketStatus::NEW);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setCreator($this->getUser());

        $em->persist($ticket);
        $em->flush();

        return $this->redirectToRoute('ticket_index');
     }

     return $this->render('ticket/new.html.twig');
   }

   #[Route('/tickets/{id}/prendre-en-charge', name: 'ticket_assign')]
   #[IsGranted('ROLE_TECHNICIEN')]
   public function assign(Ticket $ticket, EntityManagerInterface $em): Response{

        $ticket->setStatus(TicketStatus::IN_PROGRESS);
        $ticket->setTechnician($this->getUser());
        $em->flush();

        return $this->redirectToRoute('ticket_index');
   }

   #[Route('/tickets/{id}/resoudre', name: 'ticket_resolve')]
   #[IsGranted('ROLE_TECHNICIEN')]
   public function resolve(Ticket $ticket, EntityManagerInterface $em): Response{

   $ticket->setStatus(TicketStatus::RESOLVED);
   $em->flush();

   return $this->redirectToRoute('ticket_index');
   }

   #[Route('/tickets/{id}/fermer', name: 'ticket_close')]
   //#[IsGranted('ROLE_TECHNICIEN')]
   public function close(Ticket $ticket, EntityManagerInterface $em): Response{

   $ticket->setStatus(TicketStatus::CLOSED);
   $em->flush();

   return $this->redirectToRoute('ticket_index');
   }

   #[Route('/tickets/{id}/commenter', name: 'ticket_comment')]
   #[IsGranted('ROLE_USER')]
   public function addComment (Ticket $ticket, Request $request, EntityManagerInterface $em): Response{

   $comment = new Comment();
   $comment->setContent($request->request->get('content'));
   $comment->setCreatedAt(new \DateTimeImmutable());
   $comment->setTicket($ticket);
   $comment->setAuthor($this->getUser());

   $em->persist($comment);
   $em->flush();

   return $this->redirectToRoute('ticket_index');
   }
}