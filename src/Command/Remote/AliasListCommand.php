<?php

namespace Acquia\Cli\Command\Remote;

use Acquia\Cli\Command\CommandBase;
use AcquiaCloudApi\Endpoints\Applications;
use AcquiaCloudApi\Endpoints\Environments;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AliasListCommand.
 */
class AliasListCommand extends CommandBase {

  protected static $defaultName = 'remote:aliases:list';

  /**
   * {inheritdoc}.
   */
  protected function configure() {
    $this->setDescription('List all aliases for Acquia Cloud environments')
      ->setAliases(['aliases', 'sa'])
      ->addOption('cloud-app-uuid', 'uuid', InputOption::VALUE_REQUIRED, 'The UUID of the associated Acquia Cloud Application');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $acquia_cloud_client = $this->cloudApiClientService->getClient();
    $applications_resource = new Applications($acquia_cloud_client);
    $cloud_application_uuid = $this->determineCloudApplication();
    $customer_application = $applications_resource->get($cloud_application_uuid);
    $environments_resource = new Environments($acquia_cloud_client);

    $table = new Table($this->output);
    $table->setHeaders(['Environment Alias', 'Application', 'Environment UUID']);

    $site_id = $customer_application->hosting->id;
    $parts = explode(':', $site_id);
    $site_prefix = $parts[1];
    $environments = $environments_resource->getAll($customer_application->uuid);
    foreach ($environments as $environment) {
      $alias = $site_prefix . '.' . $environment->name;
      $table->addRow([$customer_application->name, $alias, $environment->uuid]);
    }

    $table->render();

    return 0;
  }

}
