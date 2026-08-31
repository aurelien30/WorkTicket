<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Ticket;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\ActivityLog;
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

         $ticket = new Ticket();
        $ticket->setTitle('Problème serveur RH');
        $ticket->setDescription('Le serveur RH est indisponible depuis plusieurs heures.');
        $ticket->setPriority('HAUTE');
        $ticket->setStatus(TicketStatus::IN_PROGRESS);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setCreator($user);
        $ticket->setTechnician($technicien);

        $manager->persist($ticket);
        
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

        $log = new ActivityLog();
        $log->setTicket($ticket);
        $log->setUser($technicien);
        $log->setMessage('a changé le statut de NEW vers IN_PROGRESS');
        $log->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($log);


        
        $project = new Project();
        $project->setName('Migration serveurs 2026');
        $project->setDescription('Renouvellement de l\'infrastructure serveur du service RH.');
        $project->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($project);

        $task1 = new Task();
        $task1->setTitle('Commander le nouveau matériel');
        $task1->setStatus('A_FAIRE');
        $task1->setCreatedAt(new \DateTimeImmutable());
        $task1->setProject($project);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Configurer le nouveau switch');
        $task2->setStatus('EN_COURS');
        $task2->setCreatedAt(new \DateTimeImmutable());
        $task2->setProject($project);
        $manager->persist($task2);

        $task3 = new Task();
        $task3->setTitle('Sauvegarder l\'ancien serveur');
        $task3->setStatus('TERMINE');
        $task3->setCreatedAt(new \DateTimeImmutable());
        $task3->setProject($project);
        $manager->persist($task3);

        $manager->flush();
    }
}