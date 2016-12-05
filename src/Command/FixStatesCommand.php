<?php

namespace Ellis\Oxid\Console\Command;

use Ellis\Oxid\Console\Util\ModuleUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixStatesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fix:states')
            ->setDefinition(array(
                new InputArgument('modules', InputArgument::IS_ARRAY, 'Module ids', array()),
                new InputOption('all', 'a', InputOption::VALUE_NONE, 'Passes all modules'),
                new InputOption('base-shop', 'b', InputOption::VALUE_NONE, 'Fix only on base shop'),
                new InputOption('shop', '', InputOption::VALUE_OPTIONAL, 'Specifies in which shop to fix states'),
            ))
            ->setDescription('Fixes modules metadata states')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command fixes information stored in database of modules.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleIds = $this->parseModuleIds($input);
        $shopConfigs = $this->parseShopConfigs($input);

        $io = new SymfonyStyle($input, $output);

        $useInstaller = class_exists('oxModuleInstaller');
        if ($useInstaller) {
            $this->fix($io, $shopConfigs, $moduleIds);
        } else {
            $this->fixLegacy($io, $shopConfigs, $moduleIds);
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param \oxConfig[]  $shopConfigs
     * @param string[]     $moduleIds
     */
    private function fix(SymfonyStyle $io, array $shopConfigs, array $moduleIds)
    {
        $stateFixer = new \oxModuleStateFixer();
        $module = oxNew('oxModule');

        foreach ($shopConfigs as $config) {
            $io->comment('Working on shop id ' . $config->getShopId());

            foreach ($moduleIds as $moduleId) {
                if (!$module->load($moduleId)) {
                    $io->note($moduleId . 'does not exist - skipping');
                    continue;
                }

                $io->comment("Fixing {$moduleId} module");
                $stateFixer->fix($module, $config);
            }
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param \oxConfig[]  $shopConfigs
     * @param string[]     $moduleIds
     */
    private function fixLegacy(SymfonyStyle $io, array $shopConfigs, array $moduleIds)
    {
        $stateFixerModule = new \oxStateFixerModule();

        foreach ($shopConfigs as $config) {
            $io->comment('Working on shop id ' . $config->getShopId());

            foreach ($moduleIds as $moduleId) {
                if (!$stateFixerModule->load($moduleId)) {
                    $io->note($moduleId . ' does not exist - skipping');
                    continue;
                }

                $io->comment("Fixing {$moduleId} module");
                $stateFixerModule->setConfig($config);
                $stateFixerModule->fixVersion();
                $stateFixerModule->fixExtendGently();
                $stateFixerModule->fixFiles();
                $stateFixerModule->fixTemplates();
                $stateFixerModule->fixBlocks();
                $stateFixerModule->fixSettings();
                $stateFixerModule->fixEvents();
            }
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return string[]
     */
    private function parseModuleIds(InputInterface $input)
    {
        $availableModuleIds = ModuleUtil::getAvailableModuleIds();
        if ($input->getOption('all')) {
            return $availableModuleIds;
        }

        $moduleIds = $input->getArgument('modules');
        if (empty($moduleIds)) {
            throw new \InvalidArgumentException('Please specify at least one module id as argument or use --all (-a) option');
        }

        foreach ($moduleIds as $moduleId) {
            if (!in_array($moduleId, $availableModuleIds)) {
                throw new \InvalidArgumentException("{$moduleId} module does not exist");
            }
        }

        return $moduleIds;
    }

    /**
     * @param InputInterface $input
     *
     * @return \oxConfig[]
     */
    private function parseShopConfigs(InputInterface $input)
    {
        if ($input->getOption('base-shop')) {
            return array(\oxRegistry::getConfig());
        }

        if ($specifiedShopId = $input->getOption('shop')) {
            if ($config = \oxSpecificShopConfig::get($specifiedShopId)) {
                return array($config);
            }

            throw new \InvalidArgumentException('Specified shop id does not exist');
        }

        return \oxSpecificShopConfig::getAll();
    }
}
