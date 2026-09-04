<?php

namespace App\Controller;
use App\Entity\KnowledgeArticle;
use App\Entity\Ticket;
use App\Enum\KnowledgeArticleStatus;
use App\Enum\TicketStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class KnowledgeArticleController extends AbstractController
{
    #[Route('tickets/{id}/creer-article', name: 'article_new_from_ticket')]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function newFromTicket(Ticket $ticket, Request $request, EntityManagerInterface $em): Response
    {
        // Crée un nouvel article à partir du ticket déjà résolu
        if ($ticket->getStatus() !== TicketStatus::RESOLVED && $ticket->getStatus() !== TicketStatus::CLOSED){
            $this->addFlash('error', 'Impossible de créer un article : ce ticket n\'est pas résolu.');
            return $this->redirectToRoute('ticket_index');
        }
        
        //ici, c'est là ou on recupère le dernier commentaire commme "solution" probable
        $comments = $ticket->getComments();
        $lastComment = count($comments) > 0 ? $comments->last() : null;

        if ($request->isMethod('POST')) {
            $article = new KnowledgeArticle();
            $article->setTitle($request->request->get('title'));
            $article->setProblem($request->request->get('problem'));
            $article->setCause($request->request->get('cause'));
            $article->setSolution($request->request->get('solution'));
            $article->setCategory($request->request->get('category'));
            $article->setStatus(KnowledgeArticleStatus::BROUILLON);
            $article->setCreatedAt(new \DateTimeImmutable());
            $article->setAuthor($this->getUser());

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('knowledge_article/new.html.twig', [
            'ticket' => $ticket,
            'suggestedSolution' => $lastComment ? $lastComment->getContent() : '',
        ]);
    }

    #[Route('/articles', name: 'article_index')]
    #[IsGranted('ROLE_USER')]

    public function index(): Response
    {
        return $this->render('knowledge_article/index.html.twig');
        
    }
}
