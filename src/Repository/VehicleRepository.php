<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    //    /**
    //     * @return Vehicle[] Returns an array of Vehicle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicle
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return [] Returns an array of Vehicle objects
     */
    public function findByVrm(string $vrm, ?\DateTimeImmutable $query_from, ?\DateTimeImmutable $query_to): ?array
    {
        $conn = $this->getEntityManager()->getConnection();

        $query_from_str = $query_from ? $query_from->format('Y-m-d H:i:s') : '';
        $query_to_str = $query_to ? $query_to->format('Y-m-d H:i:s') : '';

        $dateCondition = !empty($query_from_str) && !empty($query_to_str) ? 'AND v.time_in BETWEEN :query_from AND :query_to' : '';

        $sql = <<<SQL
            SELECT DISTINCT *, levenshtein(:vrm, v.vrm) AS distance
            FROM vehicle v
            WHERE levenshtein(:vrm, v.vrm) BETWEEN 0 AND 3 {$dateCondition}
            -- ORDER BY levenshtein(:vrm, v.vrm) ASC, time_in DESC
            ORDER BY time_in DESC
        SQL;

        $resultSet = $conn->executeQuery($sql, [
            'vrm' => $vrm,
            'query_from' => $query_from_str,
            'query_to' => $query_to_str,
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }
}
