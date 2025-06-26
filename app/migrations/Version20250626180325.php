<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626180325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // First add the column as nullable
        $this->addSql(<<<'SQL'
            ALTER TABLE issue ADD author_id INT NULL
        SQL);
        
        // Set a default author for existing issues (assuming admin user with ID 1 exists)
        $this->addSql(<<<'SQL'
            UPDATE issue SET author_id = 1 WHERE author_id IS NULL
        SQL);
        
        // Now make it NOT NULL
        $this->addSql(<<<'SQL'
            ALTER TABLE issue MODIFY author_id INT NOT NULL
        SQL);
        
        $this->addSql(<<<'SQL'
            ALTER TABLE issue ADD CONSTRAINT FK_12AD233EF675F31B FOREIGN KEY (author_id) REFERENCES admin_user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_12AD233EF675F31B ON issue (author_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_user ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_12AD233EF675F31B ON issue
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE issue DROP author_id
        SQL);
    }
}
