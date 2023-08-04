<?php

namespace App\Repository;

use App\Entity\Categories;
use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Posts>
 *
 * @method Posts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Posts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Posts[]    findAll()
 * @method Posts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Posts::class);
    }

    public function getPostsByCategories(Categories $categories, int $limit = 4, int $offset = 0)
    {
        $qb = $this->createQueryBuilder("p")
            ->where(':categories MEMBER OF p.categories_id')
            ->setParameters(array('categories' => $categories)) 
            ->setMaxResults($limit) 
            ->setFirstResult($offset)
        ;
        $data =  $qb->getQuery()->getResult();

        $totalPosts = $this->getEntityManager()->createQueryBuilder()->select('count(p.id)')->from(Posts::class, 'p')
        ->where(':categories MEMBER OF p.categories_id')
        ->setParameters(array('categories' => $categories)) 
        
        ->getQuery()->getSingleScalarResult();

        return [
            'data' => $data,
            'total' => $totalPosts
        ];



    }


//    public function findOneBySomeField($value): ?Posts
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
