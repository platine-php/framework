<?php

namespace Platine\Framework\Migration;

use Platine\Database\Schema\AlterTable;
use Platine\Framework\Migration\AbstractMigration;

class DropUserAgeField20210720080558 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->alter('users', function (AlterTable $table) {
            $table->dropColumn('age');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->alter('users', function (AlterTable $table) {
            $table->integer('age')
                 ->size('tiny')
                 ->description('The user age');
        });
    }
}
