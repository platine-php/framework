<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine\Framework\Demo;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Framework\App\Application;
use Platine\Stdlib\Helper\Str;

/**
 * Description of ConfigCommand
 *
 * @author tony
 */
class ConfigCommand extends Command
{

    protected Application $application;

    /**
     *
     */
    public function __construct(Application $application)
    {
        parent::__construct('config', 'Command to manage configuration');
        $this->setAlias('c');

        $this->addArgument('list', 'List the configuration');
        $this->addOption('-t|--type', 'Configuration type', 'app', true);

        $this->application = $application;
    }

    public function execute()
    {
        if ($this->getArgumentValue('list')) {
            $this->showConfigList();
        }
    }

    protected function showConfigList(): void
    {
        $writer = $this->io()->writer();
        /** @template T @var Config<T> $config */
        $config = $this->application->get(Config::class);
        $type = $this->getOptionValue('type');

        $writer->blackBgBlue(sprintf('Show configuration for [%s]', $type), true)->eol();

        $items = (array) $config->get($type, []);
        $rows = [];
        foreach ($items as $name => $value) {
            $valueStr = Str::stringify($value);
            if (is_int($name)) {
                $rows[] = [
                    'value' => $valueStr
                ];
            } else {
                $rows[] = [
                    'name' => $name,
                    'value' => $valueStr
                ];
            }
        }

        $writer->table($rows);

        $writer->green('Command finished successfully')->eol();
    }
}