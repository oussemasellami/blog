<?php
/**
 * Created by PhpStorm.
 * User: Oussema
 * Date: 28/03/2019
 * Time: 01:22
 */

namespace techeventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use techeventBundle\Entity\Article;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;

/**
 * Class ArticleeRepository
 * @ORM\Entity(repositoryClass="techeventBundle\Repository\ArticleeRepository")
 */

class ArticleeRepository extends EntityRepository
{
    /**
     * get all posts
     *
     * @return array
     */
    public function findAllPosts()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a
         FROM techeventBundle:Article a
      
         ORDER BY a.posted_at DESC'
            )
            ->getArrayResult();
    }

    /**
     * get one by id
     *
     * @param integer $id
     *
     * @return array
     */
    public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT a, u.username
       FROM techeventBundle:Annonce
       a JOIN a.user u
        WHERE a.id = id"
            )
            ->setParameter('id', $id)
            ->getArrayResult();
    }
    public function findPostByid($id)
    {

            return $this->getEntityManager()
                ->createQuery(
                    "SELECT p
                FROM techeventBundle:Article p
                WHERE p.id =:id"
                )
                ->setParameter('id', $id)
                ->getOneOrNullResult();

    }

    public function findEntitiesByString($str){
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p
                FROM techeventBundle:Article p
                WHERE p.titre LIKE :str'
            )
            ->setParameter('str', '%'.$str.'%')
            ->getResult();
    }


}