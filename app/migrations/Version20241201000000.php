<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Bug Tracker database schema';
    }

    public function up(Schema $schema): void
    {
        // Create admin_user table
        $this->addSql('CREATE TABLE admin_user (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            UNIQUE INDEX UNIQ_ADMIN_USER_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create category table
        $this->addSql('CREATE TABLE category (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create issue table
        $this->addSql('CREATE TABLE issue (
            id INT AUTO_INCREMENT NOT NULL,
            category_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL,
            priority VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_12AD233E12469DE2 (category_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraint
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');

        // Insert default admin user (password: admin123)
        $this->addSql('INSERT INTO admin_user (email, password, roles) VALUES (\'admin@bugtracker.com\', \'$2y$13$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\', \'["ROLE_ADMIN"]\')');

        // Insert sample categories
        $this->addSql('INSERT INTO category (name, description) VALUES 
            (\'Bug\', \'Software bugs and defects\'),
            (\'UI/UX\', \'User interface and user experience issues\'),
            (\'Performance\', \'Performance-related issues and optimizations\')
        ');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraint
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E12469DE2');

        // Drop tables
        $this->addSql('DROP TABLE issue');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE admin_user');
    }
} 