<?php

namespace App\Repository;

use App\Entity\Batch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Batch|null find($id, $lockMode = null, $lockVersion = null)
 * @method Batch|null findOneBy(array $criteria, array $orderBy = null)
 * @method Batch[]    findAll()
 * @method Batch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batch::class);
    }
    
    public function getAllFilteredQB(?Batch $filterData,$using,$year_month,Request $request)
    {
        $qb = $this->createQueryBuilder('b');
        if ($using) {
            if ($using == 1 ) {
                $qb->andWhere('b.domain IS NULL');
            }else{
                $qb->andWhere('b.domain IS NOT NULL');
            }
        }
        if ($year_month) {
            [$year,$month] = explode('-',$year_month);
            if ($month === null) {
                $month = (int) date('m');
            }
    
            if ($year === null) {
                $year = (int) date('Y');
            }
            $startDate = new \DateTimeImmutable("$year-$month-01T00:00:00");
            $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);
            $qb->andWhere('b.createdAt BETWEEN :start AND :end')
               ->setParameter('start', $startDate)
               ->setParameter('end', $endDate);
        }
        if ($filterData) {
            if ($filterData->getType()) {
                $qb->andWhere('b.type = :type')
                   ->setParameter('type', $filterData->getType()->getId());
            }
            if ($filterData->getBrand()) {
                $qb->andWhere('b.brand = :brand')
                   ->setParameter('brand', $filterData->getBrand()->getId());
            }
            if ($filterData->getModel()) {
                $qb->andWhere('b.model = :model')
                   ->setParameter('model', $filterData->getModel()->getId());
            }
            if ($filterData->getServiceCategory()) {
                $qb->andWhere('b.serviceCategory = :service_category')
                   ->setParameter('serviceCategory', $filterData->getServiceCategory()->getId());
            }
            if ($filterData->getService()) {
                $qb->andWhere('b.service = :service')
                   ->setParameter('service', $filterData->getService()->getId());
            }
            
        }
        $sort = $request->query->get('sort','b.id');
        $direction = $request->query->getAlpha('direction','desc');
        $qb->orderBy($sort, $direction);

        return $qb;
    }
    
    // /**
    //  * @return Batch[] Returns an array of Batch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Batch
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
