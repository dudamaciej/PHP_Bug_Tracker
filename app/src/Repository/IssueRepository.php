<?php

declare(strict_types=1);

/**
 * This file is part of the Bug Tracker application.
 *
 * (c) 2024 Bug Tracker Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Issue entity.
 *
 * @extends ServiceEntityRepository<Issue>
 *
 * @method Issue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Issue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Issue[]    findAll()
 * @method Issue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    /**
     * Save an Issue entity.
     */
    public function save(Issue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove an Issue entity.
     */
    public function remove(Issue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find issues with optional filtering and pagination.
     *
     * @return Issue[]
     */
    public function findIssuesWithFilter(?int $categoryId = null, int $page = 1, int $limit = 10, string $sortBy = 'createdAt', string $sortOrder = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->addSelect('c');

        if ($categoryId) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        // Validate sort field
        $allowedSortFields = ['title', 'status', 'priority', 'createdAt', 'category'];
        if (!in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 'createdAt';
        }

        // Validate sort order
        $sortOrder = 'ASC' === strtoupper($sortOrder) ? 'ASC' : 'DESC';

        // Handle special case for category sorting
        if ('category' === $sortBy) {
            $qb->orderBy('c.name', $sortOrder);
        } elseif ('priority' === $sortBy) {
            // Custom priority sorting: high > medium > low
            $qb->addSelect('CASE 
                WHEN i.priority = \'high\' THEN 1 
                WHEN i.priority = \'medium\' THEN 2 
                WHEN i.priority = \'low\' THEN 3 
                ELSE 4 END AS HIDDEN priority_order')
               ->orderBy('priority_order', 'DESC' === $sortOrder ? 'ASC' : 'DESC');
        } else {
            $qb->orderBy('i.'.$sortBy, $sortOrder);
        }

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count issues with optional filtering.
     */
    public function countIssuesWithFilter(?int $categoryId = null): int
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)');

        if ($categoryId) {
            $qb->leftJoin('i.category', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find issues by status.
     *
     * @return Issue[]
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.status = :status')
            ->setParameter('status', $status)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find issues by priority.
     *
     * @return Issue[]
     */
    public function findByPriority(string $priority): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.priority = :priority')
            ->setParameter('priority', $priority)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
