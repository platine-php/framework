<?php

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddPermissionRolesTable20210717094822 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('permissions_roles', function (CreateTable $table) {
            $table->integer('permission_id');
            $table->integer('role_id');

            $table->primary(['permission_id', 'role_id']);

            $table->foreign('permission_id')
                 ->references('permissions', 'id')
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
        $this->drop('permissions_roles');
    }
}
