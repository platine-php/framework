<?php

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddConfigTable20210708043103 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('config', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();
            $table->integer('parent_id')
                  ->description('The parent config');
            $table->string('env')
                 ->description('The config environment')
                 ->index();
            $table->string('module')
                 ->description('The module')
                 ->index()
                 ->notNull();
            $table->string('name')
                 ->description('The config name')
                  ->index();
            $table->string('value')
                 ->description('The config value');
            $table->string('type')
                 ->description('The config data type');
            $table->text('comment')
                 ->description('The config description');
            $table->integer('status')
                 ->size('tiny')
                 ->description('The config status')
                 ->defaultValue(0)
                 ->notNull();
            $table->datetime('created_at')
                  ->description('created date')
                  ->notNull();
            $table->datetime('updated_at')
                  ->description('updated date');

            $table->foreign('parent_id')
                ->references('config', 'id')
                ->onDelete('NO ACTION');

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('config');
    }
}
