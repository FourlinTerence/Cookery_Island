<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120103136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cookery_island');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cookery_island (COL 1 VARCHAR(40) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 2 VARCHAR(101) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 3 VARCHAR(26) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 4 VARCHAR(151) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 5 VARCHAR(386) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 6 VARCHAR(29) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 7 VARCHAR(29) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 8 VARCHAR(9) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 9 VARCHAR(9) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 10 VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 11 VARCHAR(19) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 12 VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 13 VARCHAR(9) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 14 VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, COL 15 VARCHAR(8) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }
}
