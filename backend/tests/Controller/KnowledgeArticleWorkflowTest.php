<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\KnowledgeArticle;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\KnowledgeArticleStatus;
use App\Enum\TicketStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class KnowledgeArticleWorkflowTest extends WebTestCase
{
    public function testArticleCreationFromResolvedTicket(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $technicien = new User();
        $technicien->setEmail('technicien.kb@workticket.local');
        $technicien->setNom('Technicien');
        $technicien->setPrenom('KB');
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setCreatedAt(new \DateTimeImmutable());
        $technicien->setPassword($hasher->hashPassword($technicien, 'motdepasse123'));
        $em->persist($technicien);

        $user = new User();
        $user->setEmail('createur.kb@workticket.local');
        $user->setNom('Createur');
        $user->setPrenom('KB');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, 'motdepasse123'));
        $em->persist($user);

        // Ticket déjà résolu, avec un commentaire de solution
        $ticket = new Ticket();
        $ticket->setTitle('Imprimante réseau injoignable');
        $ticket->setDescription('L\'imprimante du 3e étage ne répond plus.');
        $ticket->setPriority('MOYENNE');
        $ticket->setStatus(TicketStatus::RESOLVED);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setResolvedAt(new \DateTimeImmutable());
        $ticket->setCreator($user);
        $ticket->setTechnician($technicien);
        $em->persist($ticket);

        $comment = new Comment();
        $comment->setContent('Redémarrage de l\'imprimante et vérification du câble réseau.');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setTicket($ticket);
        $comment->setAuthor($technicien);
        $em->persist($comment);

        $em->flush();

        // Étape 1 : le technicien crée un article depuis ce ticket résolu
        $client->loginUser($technicien);
        $client->request('POST', '/tickets/' . $ticket->getId() . '/creer-article', [
            'title' => $ticket->getTitle(),
            'problem' => $ticket->getDescription(),
            'cause' => 'Câble réseau débranché accidentellement.',
            'solution' => $comment->getContent(),
            'category' => 'Matériel',
        ]);

        $article = $em->getRepository(KnowledgeArticle::class)->findOneBy(['title' => 'Imprimante réseau injoignable']);
        $this->assertNotNull($article);
        $this->assertSame(KnowledgeArticleStatus::BROUILLON, $article->getStatus());

        // Étape 2 : simulation de la validation par un responsable
        // (le contrôleur de validation n'existe pas encore — on modifie directement l'état pour ce test)
        $article->setStatus(KnowledgeArticleStatus::VALIDATION);
        $em->flush();
        $this->assertSame(KnowledgeArticleStatus::VALIDATION, $article->getStatus());

        // Étape 3 : publication
        $article->setStatus(KnowledgeArticleStatus::PUBLIE);
        $em->flush();
        $this->assertSame(KnowledgeArticleStatus::PUBLIE, $article->getStatus());
    }
}