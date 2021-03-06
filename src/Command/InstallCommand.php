<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Command;

use Laminas\Config\Writer\WriterInterface;
use Shlinkio\Shlink\Installer\Config\ConfigGeneratorInterface;
use Shlinkio\Shlink\Installer\Model\ImportedConfig;
use Shlinkio\Shlink\Installer\Service\InstallationCommandsRunnerInterface;
use Shlinkio\Shlink\Installer\Service\ShlinkAssetsHandler;
use Shlinkio\Shlink\Installer\Service\ShlinkAssetsHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Functional\every;
use function Functional\tail;

class InstallCommand extends Command
{
    private const POST_INSTALL_COMMANDS = [
        'db_create_schema',
        'db_migrate',
        'orm_proxies',
    ];

    private WriterInterface $configWriter;
    private ShlinkAssetsHandlerInterface $assetsHandler;
    private ConfigGeneratorInterface $configGenerator;
    private InstallationCommandsRunnerInterface $commandsRunner;
    private bool $isUpdate;

    public function __construct(
        WriterInterface $configWriter,
        ShlinkAssetsHandlerInterface $assetsHandler,
        ConfigGeneratorInterface $configGenerator,
        InstallationCommandsRunnerInterface $commandsRunner,
        bool $isUpdate
    ) {
        parent::__construct();
        $this->configWriter = $configWriter;
        $this->assetsHandler = $assetsHandler;
        $this->configGenerator = $configGenerator;
        $this->commandsRunner = $commandsRunner;
        $this->isUpdate = $isUpdate;
    }

    protected function configure(): void
    {
        $this
            ->setName('shlink:install')
            ->setDescription('Installs or updates Shlink');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text([
            '<info>Welcome to Shlink!!</info>',
            'This tool will guide you through the installation process.',
        ]);

        // Check if a cached config file exists and drop it if so
        $this->assetsHandler->dropCachedConfigIfAny($io);

        $importedConfig = $this->resolvePreviousConfig($io);
        if ($this->isUpdate) {
            $this->assetsHandler->importShlinkAssetsFromPath($io, $importedConfig->importPath());
        }
        $config = $this->configGenerator->generateConfigInteractively($io, $importedConfig->importedConfig());

        // Generate config params files
        $this->configWriter->toFile(ShlinkAssetsHandler::GENERATED_CONFIG_PATH, $config->toArray(), false);
        $io->text('<info>Custom configuration properly generated!</info>');
        $io->newLine();

        if (! $this->execPostInstallCommands($io)) {
            return -1;
        }

        $io->success('Installation complete!');
        return 0;
    }

    private function resolvePreviousConfig(SymfonyStyle $io): ImportedConfig
    {
        if ($this->isUpdate) {
            return $this->assetsHandler->resolvePreviousConfig($io);
        }

        return ImportedConfig::notImported();
    }

    private function execPostInstallCommands(SymfonyStyle $io): bool
    {
        $commands = $this->isUpdate ? tail(self::POST_INSTALL_COMMANDS) : self::POST_INSTALL_COMMANDS;

        return every($commands, fn (string $commandName) => $this->commandsRunner->execPhpCommand($commandName, $io));
    }
}
