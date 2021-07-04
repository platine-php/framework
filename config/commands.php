<?php

use Platine\Framework\Demo\Command\ConfigCommand;
use Platine\Framework\Migration\Command\MigrationCreateCommand;
use Platine\Framework\Migration\Command\MigrationExecuteCommand;
use Platine\Framework\Migration\Command\MigrationMigrateCommand;
use Platine\Framework\Migration\Command\MigrationStatusCommand;
    
    return [
        //Custom
        ConfigCommand::class,
        MigrationStatusCommand::class,
        MigrationCreateCommand::class,
        MigrationExecuteCommand::class,
        MigrationMigrateCommand::class,
    ];