<?php

namespace GoldenPlanet\GPPAppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:configure');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        // db init
        $sm = $connection->getSchemaManager();
        $tables = $sm->listTables();
        $isSchemaPresent = false;
        foreach ($tables as $table) {
            if ($table->getName() == 'installations') {
                $isSchemaPresent = true;
            }
        }

        if (!$isSchemaPresent) {
            $schema = new \Doctrine\DBAL\Schema\Schema();
            $myTable = $schema->createTable("installations");
            $myTable->addColumn("id", "integer", array("unsigned" => true, 'autoincrement' => true));
            $myTable->addColumn("shop", "string", array("length" => 256));
            $myTable->addColumn("token", "string", ['notnull' => true]);
            $myTable->addColumn("is_secure_protocol", "boolean");
            $myTable->addColumn("created_at", "datetime");
            $myTable->setPrimaryKey(array("id"));
            $queries = $schema->toSql($connection->getDatabasePlatform());
            $connection->exec($queries[0]);
        }
    }
}
