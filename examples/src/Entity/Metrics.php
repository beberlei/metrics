<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\MetricsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MetricsRepository::class)]
final readonly class Metrics
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, options: ['default' => 'gen_random_uuid()'])]
    public string $id;

    public function __construct(
        #[ORM\Column(length: 255)]
        public string $metric,

        #[ORM\Column]
        public int $measurement,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeInterface $created,
    ) {
        $this->id = Uuid::v6()->toRfc4122();
    }
}
