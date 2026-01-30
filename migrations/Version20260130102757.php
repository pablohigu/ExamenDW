<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130102757 extends AbstractMigration
{
   public function getDescription(): string
    {
        return 'Carga de datos de prueba para el examen (Clients, Activities, Songs, Bookings)';
    }

    public function up(Schema $schema): void
    {
        // 1. Clientes
        // Aseguramos que los IDs sean fijos para las pruebas
        $this->addSql("
            INSERT INTO client (id, name, email, type) VALUES 
            (1, 'Miguel Goyena', 'miguel_goyena@cuatrovientos.org', 'premium'),
            (2, 'Cliente Standard', 'standard@test.com', 'standard'),
            (3, 'Cliente Relleno', 'filler@test.com', 'standard')
        ");

        // 2. Actividades
        // BodyPump (Futura, con plazas), Spinning (Futura, LLENA), Core (Pasada), BodyPump (Pasada)
        $this->addSql("
            INSERT INTO activity (id, max_participants, type, date_start, date_end) VALUES 
            (1, 20, 'BodyPump', '2026-02-05 10:00:00', '2026-02-05 11:00:00'),
            (2, 2, 'Spinning', '2026-02-06 18:00:00', '2026-02-06 19:00:00'),
            (3, 15, 'Core', '2025-05-15 09:00:00', '2025-05-15 10:00:00'),
            (4, 15, 'BodyPump', '2025-06-20 10:00:00', '2025-06-20 11:00:00')
        ");

        // 3. Canciones
        $this->addSql("
            INSERT INTO song (id, name, duration_seconds, activity_id) VALUES 
            (1, 'Eye of the Tiger', 240, 1),
            (2, 'Levitating', 200, 1),
            (3, 'Sandstorm', 300, 2)
        ");

        // 4. Reservas
        // Reservas pasadas para Miguel (Estadísticas) y Llenado de Spinning (Actividad 2)
        $this->addSql("
            INSERT INTO booking (id, activity_id, client_id) VALUES 
            (1, 3, 1),
            (2, 4, 1),
            (3, 2, 2),
            (4, 2, 3)
        ");
    }

    public function down(Schema $schema): void
    {
        // En caso de rollback, borramos los datos
        $this->addSql('DELETE FROM booking WHERE id IN (1, 2, 3, 4)');
        $this->addSql('DELETE FROM song WHERE id IN (1, 2, 3)');
        $this->addSql('DELETE FROM activity WHERE id IN (1, 2, 3, 4)');
        $this->addSql('DELETE FROM client WHERE id IN (1, 2, 3)');
    }
}
