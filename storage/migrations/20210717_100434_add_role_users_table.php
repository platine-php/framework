<?php
namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddRoleUsersTable20210717100434 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
      $this->create('roles_users', function (CreateTable $table) {
          $table->integer('user_id');
          $table->integer('role_id');
          
          $table->primary(['user_id', 'role_id']);
          
          $table->foreign('user_id')
                ->references('users', 'id')
                ->onDelete('CASCADE');
          
          $table->foreign('role_id')
                ->references('roles', 'id')
                ->onDelete('CASCADE');

          $table->engine('INNODB');
      });
    }

    public function down(): void
    {
      //Action when migrate down
      $this->drop('roles_users');
    }
}