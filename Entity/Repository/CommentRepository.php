<?php

namespace Desarrolla2\Bundle\BlogBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Desarrolla2\Bundle\BlogBundle\Entity\Post;
use Desarrolla2\Bundle\BlogBundle\Model\CommentStatus;
use DateTime;

/**
 * CommentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommentRepository extends EntityRepository
{

    const COMMENTS_PER_PAGE = 8;

    public function getQueryForGet()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                ' SELECT c FROM BlogBundle:Comment c ' .
                ' WHERE c.status = ' . CommentStatus::PENDING .
                ' OR c.status = ' . CommentStatus::APPROVED .
                ' ORDER BY c.createdAt DESC '
                )
        ;
        return $query;
    }

    /**
     * 
     * @param int $limit
     * @return array
     */
    public function getLatestRelated(Post $post, $limit = self::POST_PER_PAGE)
    {
        $limit = (int) $limit;
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                        ' SELECT c FROM BlogBundle:Comment c ' .
                        ' JOIN c.post p ' .
                        ' JOIN p.tags t ' .
                        ' JOIN t.posts p1 ' .
                        ' WHERE c.status = ' . CommentStatus::PENDING .
                        ' OR c.status = ' . CommentStatus::APPROVED .
                        ' AND p1 = :post ' .
                        ' ORDER BY c.createdAt DESC '
                )
                ->setParameter('post', $post)
                ->setMaxResults($limit)
        ;
        $related = $query->getResult();
        if (count($related)) {
            return $related;
        } else {
            return $this->getLatest($limit);
        }
    }

    /**
     * 
     * @param int $limit
     * @return array
     */
    public function getLatest($limit = self::COMMENTS_PER_PAGE)
    {
        $limit = (int) $limit;
        $query = $this->getQueryForGet($limit);
        $query->setMaxResults($limit);
        return $query->getResult();
    }

    /**
     * @param \Desarrolla2\Bundle\BlogBundle\Entity\Post $post
     * @return \Doctrine\ORM\Query
     */
    public function getQueryForGetForPost(Post $post)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                        ' SELECT c FROM BlogBundle:Comment c ' .
                        ' WHERE ( c.status = ' . CommentStatus::PENDING . ' OR c.status = ' . CommentStatus::APPROVED . ' ) ' .
                        ' AND c.post = :post ' .
                        ' ORDER BY c.createdAt ASC '
                )
                ->setParameter('post', $post)
        ;
        return $query;
    }

    /**
     * 
     * @param \Desarrolla2\Bundle\BlogBundle\Entity\Post $post
     * @return array
     */
    public function getForPost(Post $post)
    {
        $query = $this->getQueryForGetForPost($post);
        return $query->getResult();
    }

    /**
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForFilter()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb
                ->select('c')
                ->from('BlogBundle:Comment', 'c')
                ->orderBy('c.createdAt', 'DESC')
        ;
        return $qb;
    }

    public function count()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                ' SELECT COUNT(c) FROM BlogBundle:Comment c '
                )
        ;
        return $query->getSingleScalarResult();
    }

    public function countApproved()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                ' SELECT COUNT(c) FROM BlogBundle:Comment c ' .
                ' WHERE c.status = ' . CommentStatus::APPROVED
                )
        ;
        return $query->getSingleScalarResult();
    }

    /**
     * 
     * @return type
     */
    public function getPending()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                ' SELECT c FROM BlogBundle:Comment c ' .
                ' WHERE c.status = ' . CommentStatus::PENDING .
                ' ORDER BY c.createdAt DESC '
                )
        ;
        return $query->getResult();
    }

    /**
     * Delete entity from database 
     * 
     * @param int $id
     */
    public function delete($id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
                        ' DELETE  BlogBundle:Comment c ' .
                        ' WHERE c.id = :id '
                )
                ->setParameter('id', $id)
        ;
        $query->execute();
    }

    /**
     * Count published elements from date
     * 
     * @param DateTime $date
     * @return int
     */
    public function countFromDate(DateTime $date)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
        ' SELECT COUNT(c) FROM BlogBundle:Comment c ' .
        ' WHERE c.status = ' . CommentStatus::APPROVED .
        ' AND c.createdAt >= :date '
        )
        ->setParameter('date', $date)
        ;
        return $query->getSingleScalarResult();
    }

}
