<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130112208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, max_participants INT NOT NULL, type VARCHAR(50) NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, activity_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_E00CEDDE81C06096 (activity_id), INDEX IDX_E00CEDDE19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE song (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, duration_seconds INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_33EDEEA181C06096 (activity_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA181C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');

        // Initial Data Loading
        // Clients
        $this->addSql("INSERT INTO client (id, name, email, type) VALUES (1, 'Juan Standard', 'juan@example.com', 'standard')");
        $this->addSql("INSERT INTO client (id, name, email, type) VALUES (2, 'Maria Premium', 'maria@example.com', 'premium')");
        $this->addSql("INSERT INTO client (id, name, email, type) VALUES (3, 'Carlos Standard', 'carlos@example.com', 'standard')");
        $this->addSql("INSERT INTO client (id, name, email, type) VALUES (4, 'Lucia Premium', 'lucia@example.com', 'premium')");
        $this->addSql("INSERT INTO client (id, name, email, type) VALUES (5, 'Ana Standard', 'ana@example.com', 'standard')");

        // Activities
        // Activity 1: BodyPump (Future)
        $this->addSql("INSERT INTO activity (id, max_participants, type, date_start, date_end) VALUES (1, 20, 'BodyPump', '2026-02-01 10:00:00', '2026-02-01 11:00:00')");
        // Activity 2: Spinning (Future, Small)
        $this->addSql("INSERT INTO activity (id, max_participants, type, date_start, date_end) VALUES (2, 5, 'Spinning', '2026-02-01 12:00:00', '2026-02-01 13:00:00')");
        // Activity 3: Core (Future)
        $this->addSql("INSERT INTO activity (id, max_participants, type, date_start, date_end) VALUES (3, 15, 'Core', '2026-02-02 18:00:00', '2026-02-02 18:45:00')");
        // Activity 4: BodyPump (Past)
        $this->addSql("INSERT INTO activity (id, max_participants, type, date_start, date_end) VALUES (4, 20, 'BodyPump', '2026-01-10 10:00:00', '2026-01-10 11:00:00')");
        // Activity 5: Spinning (Past)
        $this->addSql("INSERT INTO activity (id, max_participants, type, date_start, date_end) VALUES (5, 10, 'Spinning', '2026-01-12 19:00:00', '2026-01-12 20:00:00')");

        // Songs (for BodyPump act 1)
        $this->addSql("INSERT INTO song (id, name, duration_seconds, activity_id) VALUES (1, 'Warmup Track', 300, 1)");
        $this->addSql("INSERT INTO song (id, name, duration_seconds, activity_id) VALUES (2, 'Squat Track', 360, 1)");
        $this->addSql("INSERT INTO song (id, name, duration_seconds, activity_id) VALUES (3, 'Lunge Track', 240, 1)");
        // Songs (for BodyPump act 4)
        $this->addSql("INSERT INTO song (id, name, duration_seconds, activity_id) VALUES (4, 'Oldie Warmup', 300, 4)");
        $this->addSql("INSERT INTO song (id, name, duration_seconds, activity_id) VALUES (5, 'Classic Squats', 350, 4)");

        // Bookings
        // Future bookings
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (1, 2, 1)"); // Juan in Spinning
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (2, 2, 2)"); // Maria in Spinning
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (3, 1, 3)"); // Carlos in BodyPump
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (4, 3, 4)"); // Lucia in Core
        
        // Past bookings (for stats)
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (5, 4, 1)"); // Juan in old BodyPump
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (6, 5, 1)"); // Juan in old Spinning
        $this->addSql("INSERT INTO booking (id, activity_id, client_id) VALUES (7, 4, 2)"); // Maria in old BodyPump

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE81C06096');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE19EB6921');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA181C06096');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE song');
    }
}
