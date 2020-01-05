<?php

namespace App\Repository;

use App\Entity\Period;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Period|null find($id, $lockMode = null, $lockVersion = null)
 * @method Period|null findOneBy(array $criteria, array $orderBy = null)
 * @method Period[]    findAll()
 * @method Period[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Period::class);
    }


    public function findAllPeriodsPerCustomer($customer)
    {
        return $this->createQueryBuilder('p')
            ->where('p.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('p.start_time', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllPublishedPeriodsPerCustomer($customer)
    {
        return $this->createQueryBuilder('p')
            ->where('p.customer = :customer')
            ->setParameter('customer', $customer)
            ->andWhere('p.published = true')
            ->orderBy('p.start_time', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRecentPeriods()
    {
        $date = new DateTime('Now');
        $date->sub(new DateInterval('P20D'));

        return $this->createQueryBuilder('p')
            ->where('p.created_at > :date')
            ->setParameter('date', $date)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Period[] Returns an array of Period objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Period
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
