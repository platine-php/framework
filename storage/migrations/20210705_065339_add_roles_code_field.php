<?php
namespace Platine\Framework\Migration;

use Platine\Database\Schema\AlterTable;
use Platine\Framework\Migration\AbstractMigration;

class AddRolesCodeField20210705065339 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
      $this->alter('roles', function (AlterTable $table) {
          $table->string('code')->notNull();
          $table->unique('code');
      });
    }

    public function down(): void
    {
      //Action when migrate down
      $this->alter('roles', function (AlterTable $table) {
          $table->dropColumn('code');
      });
    }
}