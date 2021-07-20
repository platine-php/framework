<?php

namespace Platine\Framework\Migration;

use Platine\Database\Schema\AlterTable;
use Platine\Framework\Migration\AbstractMigration;

class AddUserRoleField20210720080304 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->alter('users', function (AlterTable $table) {
            $table->string('role')
                 ->description('The user role or function');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->alter('users', function (AlterTable $table) {
             $table->dropColumn('role');
        });
    }
}
