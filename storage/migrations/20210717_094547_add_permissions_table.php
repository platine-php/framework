<?php

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddPermissionsTable20210717094547 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('permissions', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();
            $table->string('code')
                 ->description('The permission code')
                 ->unique()
                 ->notNull();
            $table->string('description')
                 ->description('The permission description')
                 ->notNull();
            $table->string('depend')
                 ->description('The permission dependency');
            $table->datetime('created_at')
                  ->description('permission created at')
                  ->notNull();
            $table->datetime('updated_at')
                  ->description('permission updated at');

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('permissions');
    }
}
