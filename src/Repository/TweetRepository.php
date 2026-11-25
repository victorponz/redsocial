<?php

namespace App\Repository;

use App\Entity\Tweet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tweet>
 *
 * @method Tweet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tweet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tweet[]    findAll()
 * @method Tweet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TweetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tweet::class);
    }

    /**
     * Tweets más recientes primero, paginados con el Paginator de Doctrine.
     *
     * @return Paginator<Tweet>
     */
    public function getPaginated(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('t')
            ->addSelect('u')
            ->join('t.user', 'u')
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query, fetchJoinCollection: false);
    }

    /**
     * @return Tweet[] Returns an array of Tweet objects
     */
    public function getAllByHashtag($hashtag): array
    {
        //https://stackoverflow.com/questions/6574760/regex-with-doctrine-2-query-builder
        return $this->createQueryBuilder('t')->andWhere('REGEXP(t.content, :regexp) = 1')
            ->setParameter('regexp', "#$hashtag([[:cntrl:][:punct:][:space:]]|$)")
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();

    }

    //    /**
//     * @return Tweet[] Returns an array of Tweet objects
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

    //    public function findOneBySomeField($value): ?Tweet
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
