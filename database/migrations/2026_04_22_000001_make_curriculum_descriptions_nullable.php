<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $tables = [
        'topic', 'component', 'standard',
        'affirmation_dna_dba', 'evidence_dna_dba',
        'competence', 'didactic_unit',
    ];

    private function rawPdo(): PDO
    {
        $cfg = config('database.connections.mysql');
        return new PDO(
            "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']}",
            $cfg['username'],
            $cfg['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]
        );
    }

    public function up(): void
    {
        $pdo = $this->rawPdo();
        foreach ($this->tables as $table) {
            $pdo->exec("ALTER TABLE `{$table}` MODIFY `description` VARCHAR(255) NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        $pdo = $this->rawPdo();
        foreach ($this->tables as $table) {
            $pdo->exec("UPDATE `{$table}` SET `description` = '' WHERE `description` IS NULL");
            $pdo->exec("ALTER TABLE `{$table}` MODIFY `description` VARCHAR(255) NOT NULL DEFAULT ''");
        }
    }
};
