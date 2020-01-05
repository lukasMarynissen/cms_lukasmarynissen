<?php

namespace App\Repository;

use App\Entity\Activity;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    // /**
    //  * @return Activity[] Returns an array of Activity objects
    //  */

    public function findAllActivitiesInWeek($year, $weekNr)
    {

        $thisMonday = new \DateTime(date('Y-m-d',strtotime($year.'W'.$weekNr)));
        $nextMonday = new \DateTime( $thisMonday->format('Y-m-d').' next Monday');

        return $this->createQueryBuilder('a')
            ->where('a.start_time > :thisMonday')
            ->setParameter('thisMonday', $thisMonday)
            ->andWhere('a.start_time < :nextMonday')
            ->setParameter('nextMonday', $nextMonday)
            ->orderBy('a.start_time', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllActivitiesInPeriodByCustomer($startTime, $endTime, $customer_id)
    {

        return $this->createQueryBuilder('a')
            ->where('a.start_time > :startTime')
            ->setParameter('startTime', $startTime)
            ->andWhere('a.start_time < :endTime')
            ->setParameter('endTime', $endTime)
            ->andWhere('a.customer_id = :customer_id')
            ->setParameter('customer_id', $customer_id)
            ->orderBy('a.start_time', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRecentActivities()
    {
        $date = new DateTime('Now');
        $date->sub(new DateInterval('P20D'));

        return $this->createQueryBuilder('a')
            ->where('a.start_time > :date')
            ->setParameter('date', $date)
            ->orderBy('a.start_time', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRecentActivitiesByWorker($worker)
    {
        $date = new DateTime('Now');
        $date->sub(new DateInterval('P20D'));

        return $this->createQueryBuilder('a')
            ->where('a.start_time > :date')
            ->setParameter('date', $date)
            ->andWhere('a.user = :worker')
            ->setParameter('worker', $worker)
            ->orderBy('a.start_time', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllUserActivitiesInWeek($year, $weekNr, $user)
    {

        $thisMonday = new \DateTime(date('Y-m-d',strtotime($year.'W'.$weekNr)));
        $nextMonday = new \DateTime( $thisMonday->format('Y-m-d').' next Monday');

        return $this->createQueryBuilder('a')
            ->where('a.start_time > :thisMonday')
            ->setParameter('thisMonday', $thisMonday)
            ->andWhere('a.start_time < :nextMonday')
            ->setParameter('nextMonday', $nextMonday)
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.start_time', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllUserActivitiesInPeriod($startTime, $endTime, $user_id)
    {

        return $this->createQueryBuilder('a')
            ->where('a.start_time > :startTime')
            ->setParameter('startTime', $startTime)
            ->andWhere('a.start_time < :endTime')
            ->setParameter('endTime', $endTime)
            ->andWhere('a.user_id = :user_id')
            ->setParameter('user_id', $user_id)
            ->orderBy('a.start_time', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }



    /*
    public function findOneBySomeField($value): ?Activity
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


}
