<?php

namespace Ellis\Oxid\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Updates OXID database views command.
 */
class DatabaseUpdateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('database:update')
            ->setAliases(array('db:update'))
            ->setDescription('Updates database views')
            ->setHelp(<<<'EOF'
Command <info>%command.name%</info> updates OXID shop database views

If there are some changes in database schema it is always a good idea to run
database update command
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->comment('Updating database views');

        /** @var oxDbMetaDataHandler $dbHandler */
        $dbHandler = \oxNew('oxDbMetaDataHandler');

        if (!$dbHandler->updateViews()) {
            throw new \RuntimeException('Could not update database views');
        }

        $io->success('Database views updated successfully');
    }
}
