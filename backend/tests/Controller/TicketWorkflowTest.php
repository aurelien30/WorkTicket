<?php

namespace App\Tests\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TicketWorkflowTest extends WebTestCase
{
    public function testFullTicketWorkflow(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // Création d'un utilisateur et d'un technicien pour le test
        $user = new User();
        $user->setEmail('utilisateur.test@workticket.local');
        $user->setNom('Utilisateur');
        $user->setPrenom('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, 'motdepasse123'));
        $em->persist($user);

        $technicien = new User();
        $technicien->setEmail('technicien.test@workticket.local');
        $technicien->setNom('Technicien');
        $technicien->setPrenom('Test');
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setCreatedAt(new \DateTimeImmutable());
        $technicien->setPassword($hasher->hashPassword($technicien, 'motdepasse123'));
        $em->persist($technicien);

        $em->flush();

        // 1. L'utilisateur se connecte et crée un ticket
        $client->loginUser($user);
        $client->request('POST', '/tickets/new', [
            'title' => 'Écran ne s\'allume plus',
            'description' => 'Après mise à jour Windows, l\'écran reste noir.',
            'priority' => 'HAUTE',
        ]);

        // 2. On vérifie que le ticket existe bien en base
        $ticket = $em->getRepository(Ticket::class)->findOneBy(['title' => 'Écran ne s\'allume plus']);
        $this->assertNotNull($ticket);
        $this->assertSame(TicketStatus::NEW, $ticket->getStatus());

        // 3. Le technicien prend le ticket en charge
        $client->loginUser($technicien);
        $client->request('GET', '/tickets/' . $ticket->getId() . '/prendre-en-charge');

        $em->refresh($ticket);
        $this->assertSame(TicketStatus::IN_PROGRESS, $ticket->getStatus());

        // 4. Le technicien résout le ticket
        $client->request('GET', '/tickets/' . $ticket->getId() . '/resoudre');
        $em->refresh($ticket);
        $this->assertSame(TicketStatus::RESOLVED, $ticket->getStatus());

        // 5. Le technicien ferme le ticket
        $client->request('GET', '/tickets/' . $ticket->getId() . '/fermer');
        $em->refresh($ticket);
        $this->assertSame(TicketStatus::CLOSED, $ticket->getStatus());
    }
}