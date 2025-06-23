<?php
declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250615101026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables for tasks and users (UUID IDs, FOSUserBundle compatible)';
    }

    public function up(Schema $schema): void
    {
        // Tabela `task`
        $this->addSql('
            CREATE TABLE task (
                id BINARY(16) NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                deadline DATETIME NOT NULL,
                status VARCHAR(255) NOT NULL,
                parent_task_id BINARY(16) DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT fk_task_parent_task FOREIGN KEY (parent_task_id) REFERENCES task (id)
            )
        ');

        // Tabela `user`
        $this->addSql('
            CREATE TABLE user (
                id BINARY(16) NOT NULL,
                username VARCHAR(180) NOT NULL,
                username_canonical VARCHAR(180) NOT NULL,
                email VARCHAR(180) NOT NULL,
                email_canonical VARCHAR(180) NOT NULL,
                enabled TINYINT(1) NOT NULL,
                salt VARCHAR(255) DEFAULT NULL,
                password VARCHAR(255) NOT NULL,
                last_login DATETIME DEFAULT NULL,
                confirmation_token VARCHAR(180) DEFAULT NULL,
                password_requested_at DATETIME DEFAULT NULL,
                roles TEXT NOT NULL,
                UNIQUE INDEX UNIQ_USER_USERNAME (username),
                UNIQUE INDEX UNIQ_USER_USERNAME_CANONICAL (username_canonical),
                UNIQUE INDEX UNIQ_USER_EMAIL (email),
                UNIQUE INDEX UNIQ_USER_EMAIL_CANONICAL (email_canonical),
                UNIQUE INDEX UNIQ_USER_CONFIRMATION_TOKEN (confirmation_token),
                PRIMARY KEY(id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
    }
}
