<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Enum\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function countByStatus(TicketStatus $status): int
{
    return $this->createQueryBuilder('t')
        ->select('COUNT(t.id)')
        ->andWhere('t.status = :status')
        ->setParameter('status', $status)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getAverageResolutionTimeInHours(): ?float
{
    $tickets = $this->createQueryBuilder('t')
        ->andWhere('t.resolvedAt IS NOT NULL')
        ->getQuery()
        ->getResult();

    if (count($tickets) === 0) {
        return null;
    }

    $totalHours = 0;
    foreach ($tickets as $ticket) {
        $diff = $ticket->getCreatedAt()->diff($ticket->getResolvedAt());
        $totalHours += ($diff->days * 24) + $diff->h;
    }

    return round($totalHours / count($tickets), 1);
}//    /**
    //     * @return Ticket[] Returns an array of Ticket objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ticket
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
