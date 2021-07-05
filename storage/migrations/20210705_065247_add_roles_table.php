<?php
namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddRolesTable20210705065247 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
      $this->create('roles', function (CreateTable $table) {
          $table->integer('id')
                  ->autoincrement()
                 ->primary();
          $table->string('description')
                 ->description('The role description');
          $table->datetime('created_at')
                  ->description('role created at')
                  ->notNull();
          $table->datetime('updated_at')
                  ->description('role updated at');

          $table->engine('INNODB');
      });
    }

    public function down(): void
    {
      //Action when migrate down
      $this->drop('roles');
    }
}