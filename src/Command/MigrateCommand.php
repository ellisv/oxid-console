<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ellis\Oxid\Console\Command;

use Ellis\Oxid\Console\Backport\OutputAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Migrate Command.
 *
 * Runs migration handler with input timestamp. If no timestamp were passed
 * runs with current timestamp instead.
 */
class MigrateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDefinition(array(
                new InputArgument('timestamp', InputArgument::OPTIONAL, 'Timestamp', null),
            ))
            ->setDescription('Run migration scripts')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command runs migration scripts for given timestamp.
If no timestamp is passed than it assumes timestamp is current time.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timestamp = $input->getArgument('timestamp');
        if ($timestamp !== null) {
            $timestamp = $this->normalizeTimestamp($timestamp);
        }

        $io = new SymfonyStyle($input, $output);
        $io->comment('Running migration scripts');

        $migrationHandler = new \oxMigrationHandler();
        // TODO: Eventually OutputAdapter shouldn't be used here.
        $migrationHandler->run($timestamp, new OutputAdapter($output));

        $io->success('Migration finished successfully');
    }

    /**
     * @param string $timestamp
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function normalizeTimestamp($timestamp)
    {
        if (\oxMigrationQuery::isValidTimestamp($timestamp)) {
            return $timestamp;
        }

        if ($time = strtotime($timestamp)) {
            return date('YmdHis', $time);
        }

        throw new \InvalidArgumentException('Invalid timestamp format, use YYYYMMDDhhmmss format');
    }
}
