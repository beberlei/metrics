<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Metrics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Metrics>
 *
 * @method Metrics|null find($id, $lockMode = null, $lockVersion = null)
 * @method Metrics|null findOneBy(array $criteria, array $orderBy = null)
 * @method Metrics[]    findAll()
 * @method Metrics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetricsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metrics::class);
    }
}
