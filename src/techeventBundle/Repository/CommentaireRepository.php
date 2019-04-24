<?php
/**
 * Created by PhpStorm.
 * User: Oussema
 * Date: 28/03/2019
 * Time: 01:24
 */

namespace techeventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use techeventBundle\Entity\Commentaire;
use techeventBundle\Entity\Article;

class CommentaireRepository extends EntityRepository
{

    /**
     * get post Commentaire
     *
     * @param integer $post_id
     *
     * @return array
     */
    public function getPostComments($post_id){
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c, u.username
       FROM techeventBundle:Commentaire c
       JOIN c.user u
       WHERE c.post = :id"
            )
            ->setParameter('id', $post_id)
            ->getArrayResult();
    }

}