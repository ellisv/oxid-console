<?php

namespace Ellis\Oxid\Console\Command;

use Ellis\Oxid\Console\Util\PathUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateMigrationCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:migration')
            ->setDefinition(array(
                new InputArgument('words', InputArgument::IS_ARRAY, 'Description of a migration', array()),
            ))
            ->setAliases(array('g:migration'))
            ->setDescription('Generate new migration file')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command generates blank migration class.

Migration name depends on words you have written. If no words were passed you will be asked to input them.
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

        $migrationDir = PathUtil::join(OX_BASE_PATH, 'migration');
        if (!is_dir($migrationDir)) {
            $io->note('Migrations directory does not exist. Creating one...');
            mkdir($migrationDir);
        }

        $words = $input->getArgument('words');
        if (empty($words)) {
            $words = explode(' ', $io->ask('Enter short description'));
        }

        $migrationName = $this->buildMigrationName($words);
        $fileName = sprintf('%d_%s.php', \oxMigrationQuery::getCurrentTimestamp(), strtolower($migrationName));

        $this->generate(PathUtil::join($migrationDir, $fileName), $migrationName);
        $this->ensureHtaccessPresent($migrationDir);

        $io->success(sprintf('Successfully generated %s', $fileName));
    }

    /**
     * @param string[] $tokens
     *
     * @return string
     */
    private function buildMigrationName(array $tokens)
    {
        return array_reduce($tokens, function ($migrationName, $token) {
            return $migrationName . ucfirst($token);
        }, '');
    }

    /**
     * @param string $path
     * @param string $name
     */
    private function generate($path, $name)
    {
        $smarty = \oxRegistry::get('oxUtilsView')->getSmarty();
        $smarty->assign('name', $name);
        $content = $smarty->fetch(PathUtil::resourcePath('migration.php.tpl'));

        file_put_contents($path, $content);
    }

    /**
     * Ensure .htaccess file is present in given directory.
     *
     * @param string $directory
     */
    private function ensureHtaccessPresent($directory)
    {
        $htaccessPath = PathUtil::join($directory, '.htaccess');
        if (!file_exists($htaccessPath)) {
            $htaccess = <<<'EOF'
# disabling file access
<FilesMatch .*>
order allow,deny
deny from all
</FilesMatch>

Options -Indexes
EOF;

            file_put_contents($htaccessPath, $htaccess);
        }
    }
}
