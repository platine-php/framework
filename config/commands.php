<?php

use Platine\Framework\Demo\Command\ConfigCommand;
use Platine\Framework\Demo\Command\RouteCommand;
use Platine\Framework\Migration\Command\MigrationCreateCommand;
use Platine\Framework\Migration\Command\MigrationExecuteCommand;
use Platine\Framework\Migration\Command\MigrationMigrateCommand;
use Platine\Framework\Migration\Command\MigrationResetCommand;
use Platine\Framework\Migration\Command\MigrationStatusCommand;
    
    return [
        //Framework
        MigrationStatusCommand::class,
        MigrationCreateCommand::class,
        MigrationExecuteCommand::class,
        MigrationMigrateCommand::class,
        MigrationResetCommand::class,
        
        //Custom
        ConfigCommand::class,
        RouteCommand::class,
    ];