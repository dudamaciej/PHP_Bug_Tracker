<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625204724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Step 1: Add columns, created_at as nullable
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_user ADD first_name VARCHAR(100) DEFAULT NULL, ADD last_name VARCHAR(100) DEFAULT NULL, ADD phone VARCHAR(20) DEFAULT NULL, ADD bio LONGTEXT DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD last_login_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        // Step 2: Set created_at for existing rows
        $this->addSql(<<<'SQL'
            UPDATE admin_user SET created_at = NOW() WHERE created_at IS NULL
        SQL);
        // Step 3: Make created_at NOT NULL
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_user MODIFY created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_user RENAME INDEX uniq_admin_user_email TO UNIQ_AD8A54A9E7927C74
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_user DROP first_name, DROP last_name, DROP phone, DROP bio, DROP created_at, DROP last_login_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_user RENAME INDEX uniq_ad8a54a9e7927c74 TO UNIQ_ADMIN_USER_EMAIL
        SQL);
    }
}
