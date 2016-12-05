<?php

namespace Ellis\Oxid\Console\Command;

use Ellis\Oxid\Console\Util\ModuleUtil;
use Ellis\Oxid\Console\Util\PathUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateModuleCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:module')
            ->setAliases(array('g:module'))
            ->setDescription('Generate new module scaffold')
            ->setHelp('The <info>%command.name%</info> generates module scaffold');
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $scaffold = $this->collect($io);
        $this->generate($scaffold);

        $io->success('Module generated successfully');
    }

    /**
     * Collect information about upcoming module.
     *
     * @param SymfonyStyle $io
     *
     * @return \stdClass
     */
    private function collect(SymfonyStyle $io)
    {
        $notRequired = function ($val) {
            return trim($val);
        };

        $vendor = trim(strtolower($io->ask('Vendor Prefix', '', $notRequired)));
        file_put_contents('/tmp/testout', 'reached');

        $initial = true;
        do {
            if (!$initial) {
                $io->error('Module path or id is taken with given title');
            }

            $initial = false;
            $title = $io->ask('Module Title');
            $name = str_replace(' ', '', ucwords($title));
            $id = $vendor . strtolower($name);
        } while (ModuleUtil::exists($id) || !ModuleUtil::isPathAvailable($vendor, $name));

        return (object) array(
            'vendor' => $vendor,
            'title' => $title,
            'name' => $name,
            'id' => $id,
            'author' => $io->ask('Author', '', $notRequired),
            'url' => $io->ask('Url', '', $notRequired),
            'email' => $io->ask('Email', '', $notRequired),
        );
    }

    private function generate(\stdClass $scaffold)
    {
        if ($scaffold->vendor) {
            $this->ensureVendorDirectoryPresent($scaffold->vendor);
        }

        $templateDir = PathUtil::resourcePath('module');
        $moduleDir = ModuleUtil::buildDirectoryPath($scaffold->vendor, $scaffold->name);

        if (!file_exists($moduleDir)) {
            mkdir($moduleDir);
        }

        $map = array('_prefix_' => $scaffold->id);
        $fileIter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($templateDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($fileIter as $fileInfo) {
            $path = (string) $fileInfo;
            $replace = array(
                'search' => array_merge(array($templateDir), array_keys($map)),
                'replace' => array_merge(array($moduleDir), array_values($map)),
            );

            $targetPath = str_replace($replace['search'], $replace['replace'], $path);
            $targetPath = preg_replace('/\.tpl$/', '', $targetPath);

            @mkdir(dirname($targetPath), 0777, true);

            $this->render($path, $targetPath, $scaffold);
        }
    }

    private function render($templatePath, $targetPath, $scaffold)
    {
        if (preg_match('/\.tpl$/', $templatePath)) {
            $smarty = \oxRegistry::get('oxUtilsView')->getSmarty();
            $smarty->assign('scaffold', $scaffold);
            $content = $smarty->fetch($templatePath);
        } else {
            $content = file_get_contents($templatePath);
        }

        file_put_contents($targetPath, $content);
    }

    /**
     * @param string $vendor
     */
    private function ensureVendorDirectoryPresent($vendor)
    {
        $dir = ModuleUtil::buildDirectoryPath($vendor, '');
        if (!file_exists($dir)) {
            mkdir($dir);
            file_put_contents(PathUtil::join($dir, 'vendormetadata.php'), '<?php'.PHP_EOL);
        }
    }
}
