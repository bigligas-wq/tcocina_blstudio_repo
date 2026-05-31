<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $newRoles = ['customer', 'admin', 'kitchen', 'delivery', 'developer', 'cajero'];
    private array $oldRoles = ['customer', 'admin', 'kitchen', 'delivery'];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $list = "'" . implode("','", $this->newRoles) . "'";
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM($list) NOT NULL DEFAULT 'customer'");
            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteRoleCheck($this->newRoles);
            return;
        }

        throw new \RuntimeException("Unsupported driver for role enum migration: {$driver}");
    }

    public function down(): void
    {
        // Si hay usuarios con rol nuevo, los degradamos a customer antes de achicar el enum
        DB::table('users')
            ->whereIn('role', ['developer', 'cajero'])
            ->update(['role' => 'admin']);

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $list = "'" . implode("','", $this->oldRoles) . "'";
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM($list) NOT NULL DEFAULT 'customer'");
            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteRoleCheck($this->oldRoles);
            return;
        }

        throw new \RuntimeException("Unsupported driver for role enum migration: {$driver}");
    }

    /**
     * En SQLite no se puede ALTER un CHECK constraint: hay que recrear la tabla.
     * Tomamos el CREATE TABLE actual, reemplazamos solo el CHECK del role,
     * copiamos los datos y restauramos índices.
     */
    private function rebuildSqliteRoleCheck(array $roles): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            DB::transaction(function () use ($roles) {
                $current = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='users'");
                if (!$current) {
                    throw new \RuntimeException('users table not found');
                }

                $list = "'" . implode("', '", $roles) . "'";

                // Reemplaza el CHECK del role preservando el resto del CREATE TABLE intacto
                $newSql = preg_replace(
                    '/check\s*\(\s*"role"\s+in\s*\([^)]*\)\s*\)/i',
                    "check (\"role\" in ($list))",
                    $current->sql
                );

                // Renombra la tabla temporalmente
                $newSql = preg_replace('/CREATE TABLE\s+"users"/i', 'CREATE TABLE "users_tmp_rolemig"', $newSql);

                DB::statement($newSql);

                $cols = collect(DB::select('PRAGMA table_info(users)'))
                    ->pluck('name')
                    ->map(fn ($c) => "\"$c\"")
                    ->implode(', ');

                DB::statement("INSERT INTO users_tmp_rolemig ($cols) SELECT $cols FROM users");
                DB::statement('DROP TABLE users');
                DB::statement('ALTER TABLE users_tmp_rolemig RENAME TO users');

                // Restaurar índices (los que estaban antes en la BD local)
                DB::statement('CREATE UNIQUE INDEX "users_email_unique" ON "users" ("email")');
                DB::statement('CREATE INDEX "users_email_is_active_index" ON "users" ("email", "is_active")');
                DB::statement('CREATE INDEX "users_role_index" ON "users" ("role")');
            });
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
};
