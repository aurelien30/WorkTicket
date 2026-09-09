<?php

namespace App\Repository;

use App\Entity\KnowledgeArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<KnowledgeArticle>
 */
class KnowledgeArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KnowledgeArticle::class);
    }

    
     public function searchFullText(string $query): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
        SELECT id, title, problem, solution, category
        FROM knowledge_article
        WHERE MATCH(title, problem, solution) AGAINST (:query IN NATURAL LANGUAGE MODE)
        AND status = :status
        ORDER BY MATCH(title, problem, solution) AGAINST (:query IN NATURAL LANGUAGE MODE) DESC
    ';

    
    $result = $conn->executeQuery($sql, [
        'query' => $query,
        'status' => 'PUBLIE',
    ]);

    return $result->fetchAllAssociative();
}
    //    /**
    //     * @return KnowledgeArticle[] Returns an array of KnowledgeArticle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('k.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?KnowledgeArticle
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
