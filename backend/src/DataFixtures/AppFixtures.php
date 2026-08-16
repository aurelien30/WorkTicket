<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Ticket;
use App\Entity\Comment;
use App\Enum\TicketStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('jean.dupont@workticket.local');
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'motdepasse123')
        );

        $manager->persist($user);
        $manager->flush();


        $ticket = new Ticket();
        $ticket->setTitle('Problème serveur RH');
        $ticket->setDescription('Le serveur RH est indisponible depuis plusieurs heures.');
        $ticket->setPriority('HAUTE');
        $ticket->setStatus(TicketStatus::NEW);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setCreator($user);

        $manager->persist($ticket);
        $manager->flush();


        $technicien = new User;
        $technicien->setEmail('marie.martin@workticket.local');
        $technicien->setNom('Martin');
        $technicien->setPrenom('Marie');
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setCreatedAt(new \DateTimeImmutable());
        $technicien->setPassword(
            $this->passwordHasher->hashPassword($technicien, 'motdepasse456')
        );

        $manager->persist($technicien);
        
        $commentTech = new Comment();
        $commentTech->setContent('Nous avons identifié le problème et travaillons à sa résolution.');
        $commentTech->setCreatedAt(new \DateTimeImmutable());
        $commentTech->setTicket($ticket);
        $commentTech->setAuthor($technicien);
        $manager->persist($commentTech);

        $commentUser = new Comment();
        $commentUser->setContent('Merci pour la mise à jour. J\'attends avec impatience la résolution du problème.');
        $commentUser->setCreatedAt(new \DateTimeImmutable());
        $commentUser->setTicket($ticket);
        $commentUser->setAuthor($user);
        $manager->persist($commentUser);

        $manager->flush();
        
    }
}