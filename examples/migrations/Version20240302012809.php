<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240302012809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create metrics table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE metrics (id UUID DEFAULT gen_random_uuid() NOT NULL, metric VARCHAR(255) NOT NULL, measurement INT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN metrics.created IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE metrics');
    }
}
