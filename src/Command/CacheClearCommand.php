<?php

namespace Ellis\Oxid\Console\Command;

use Ellis\Oxid\Console\Util\PathUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Cache Clear command.
 *
 * Clears out OXID cache from tmp folder.
 */
class CacheClearCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDefinition(array(
                new InputOption('smarty', 's', InputOption::VALUE_NONE, 'Clears out smarty cache'),
                new InputOption('files', 'f', InputOption::VALUE_NONE, 'Clears out files cache'),
                new InputOption('oxcache', 'o', InputOption::VALUE_NONE, 'Clears out oxCache (EE versions)'),
            ))
            ->setDescription('Clear OXID cache from tmp folder')
            ->setHelp(<<<'EOF'
This command <info>%command.name%</info> clears out contents of OXID tmp folder

It applies following rules:
 * Does not delete .htaccess
 * Does not delete smarty directory but its contents by default
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
        $io->comment('Clearing OXID cache...');

        $all = !$input->getOption('smarty') && !$input->getOption('files') && !$input->getOption('oxcache');

        if (($all || $input->getOption('oxcache')) && class_exists('oxCache')) {
            \oxRegistry::get('oxCache')->reset(false);
        }

        if ($all || $input->getOption('smarty')) {
            PathUtil::rmtree(PathUtil::join($this->fetchCacheDir(), 'smarty'));
        }

        if ($all || $input->getOption('files')) {
            PathUtil::rmtree($this->fetchCacheDir(), array('.htaccess', 'smarty'));
        }

        $io->success('Cache cleared successfully');
    }

    /**
     * @return string
     *
     * @throws \RuntimeException Whenever cache directory is not present
     */
    private function fetchCacheDir()
    {
        $cacheDir = \oxRegistry::getConfig()->getConfigParam('sCompileDir');
        if (!is_dir($cacheDir)) {
            throw new \RuntimeException('Seems that compile directory does not exist');
        }

        return $cacheDir;
    }
}
