<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ActivityLogger
{
    public function __construct(private EntityManagerInterface $em) 
    {
    }

    public function log(Ticket $ticket, User $user, string $message): void
    {
        $Log = new ActivityLog();
        $Log->setTicket($ticket);
        $Log->setUser($user);
        $Log->setMessage($message);
        $Log->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($Log);
        $this->em->flush();
    }
}