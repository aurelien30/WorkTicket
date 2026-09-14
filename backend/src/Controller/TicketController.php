<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Comment;
use App\Enum\TicketStatus;
use App\Service\ActivityLogger;
use App\Repository\TicketRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
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
public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response{

     $ticket = new Ticket();

     if ($request->isMethod('POST')) {
        $ticket->setTitle($request->request->get('title'));
        $ticket->setDescription($request->request->get('description'));
        $ticket->setPriority($request->request->get('priority'));
        $ticket->setStatus(TicketStatus::NEW);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setCreator($this->getUser());
        
        $errors = $validator->validate($ticket);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->render('ticket/new.html.twig');
        }
        $em->persist($ticket);
        $em->flush();

        return $this->redirectToRoute('ticket_index');
     }

     return $this->render('ticket/new.html.twig');
   }

   #[Route('/tickets/{id}/prendre-en-charge', name: 'ticket_assign')]
   #[IsGranted('ROLE_TECHNICIEN')]
   public function assign(Ticket $ticket, EntityManagerInterface $em, ActivityLogger $logger): Response{

        $ancienStatus = $ticket->getStatus()->value;

        $ticket->setStatus(TicketStatus::IN_PROGRESS);
        $ticket->setTechnician($this->getUser());
        $em->flush();

       $logger->log(
        $ticket,
        $this->getUser(),
        sprintf('a changé le statut de %s vers %s', $ancienStatus, TicketStatus::IN_PROGRESS->value)
    );

        return $this->redirectToRoute('ticket_index');
   }

   #[Route('/tickets/{id}/resoudre', name: 'ticket_resolve')]
   #[IsGranted('ROLE_TECHNICIEN')]
   public function resolve(Ticket $ticket, EntityManagerInterface $em, ActivityLogger $logger): Response{

    $ancienStatus = $ticket->getStatus()->value;

   $ticket->setStatus(TicketStatus::RESOLVED);
   $ticket->setResolvedAt(new \DateTimeImmutable());
   $em->flush();

   $logger->log(
        $ticket,
        $this->getUser(),
        sprintf('a changé le statut de %s vers %s', $ancienStatus, TicketStatus::RESOLVED->value)
    );

   return $this->redirectToRoute('ticket_index');
   }

   #[Route('/tickets/{id}/fermer', name: 'ticket_close')]
   #[IsGranted('ROLE_TECHNICIEN')]
   public function close(Ticket $ticket, EntityManagerInterface $em, ActivityLogger $logger): Response{

   $ancienStatus = $ticket->getStatus()->value;

   $ticket->setStatus(TicketStatus::CLOSED);
   $em->flush();

   $logger->log(
        $ticket,
        $this->getUser(),
        sprintf('a changé le statut de %s vers %s', $ancienStatus, TicketStatus::CLOSED->value)
    );

   return $this->redirectToRoute('ticket_index');
   }

   #[Route('/tickets/{id}/commenter', name: 'ticket_comment')]
   //#[IsGranted('ROLE_USER')]
   public function addComment (Ticket $ticket, 
   Request $request, 
   EntityManagerInterface $em, 
   CsrfTokenManagerInterface $csrfTokenManager,
   SluggerInterface $slugger
   ): Response{

   $submittedToken = $request->request->get('_token');
   if (!$csrfTokenManager->isTokenValid(new CsrfToken('comment_' . $ticket->getId(), $submittedToken))) {
       throw $this->createAccessDeniedException('Invalid CSRF token.');
   }
   $comment = new Comment();
   $comment->setContent($request->request->get('content'));
   $comment->setCreatedAt(new \DateTimeImmutable());
   $comment->setTicket($ticket);
    $comment->setAuthor($this->getUser());

   /** @var UploadedFile|null $attachment */
    $attachment = $request->files->get('attachment');

    if ($attachment) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!in_array($attachment->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', 'Seuls les fichiers JPG, PNG et PDF sont autorisés.');
            return $this->redirectToRoute('ticket_index');
        }

        if ($attachment->getSize() > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le fichier ne doit pas dépasser 5 Mo.');
            return $this->redirectToRoute('ticket_index');
        }

        $originalFilename = pathinfo($attachment->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $attachment->guessExtension();

        try {
            $attachment->move($this->getParameter('uploads_directory'), $newFilename);
            $comment->setAttachmentFilename($newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi du fichier.');
        }
    }
   $em->persist($comment);
   $em->flush();

   return $this->redirectToRoute('ticket_index');
   }

 

}