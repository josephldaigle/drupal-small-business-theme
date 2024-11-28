<?php

namespace Drupal\small_business_theme\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;


/**
 * Defines Drush commands for the Small Business Theme.
 */
class SmbThemeInstall extends DrushCommands
{
  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new SmbThemeInstall object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory)
  {
    parent::__construct();
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Installs the theme's dependencies.
   *
   * @command smb:install
   * @aliases smb-install
   * @usage drush smb:install
   *   Installs theme dependencies like TailwindCSS.
   *
   * @return int
   * The exit status code.
   */
  public function install()
  {
    $this->output()->writeln('Starting theme dependency installation...');

    try {
      $themePath = DRUPAL_ROOT . '/themes/custom/small_business_theme';
      chdir($themePath);

      // Check for node version and if >= 22.11.0, don't install.
      $nodeVersion = exec('node -v');
      if (version_compare($nodeVersion, '12.11.0', '>=')) {
        $this->output()->writeln('Compatible Node.js version detected (' . $nodeVersion . '). Skipping install..');
      } else {
        $this->output()->writeln('Incompatible Node.js version detected (' . $nodeVersion . '). Installing Node.js...');
        // Install Node.js
        $output = [];
        $status = null;
        exec('nvm install 12.11.0', $output, $status);
        if ($status !== 0) {
          throw new \Exception('Failed to install Node.js: ' . implode("\n", $output));
        }
        $this->output()->writeln('Node.js installed successfully.');
      }

      if (!`which node`) {
        throw new \Exception('Node.js is not installed or not available in PATH.');
      }

      // Install node
      $this->output()->writeln('Running npm install...');
      $output = [];
      $status = null;

      exec('npm install', $output, $status);
      if ($status !== 0) {
        throw new \Exception('Failed to run npm install: ' . implode("\n", $output));
      }
      $this->output()->writeln('Dependencies installed successfully.');

      $this->output()->writeln('Running npm run build...');
      exec('npm run build', $output, $status);
      if ($status !== 0) {
        throw new \Exception('Failed to build assets: ' . implode("\n", $output));
      }

    } catch (\Exception $e) {
      $this->loggerFactory->get('small_business_theme')->error('Output: ' . implode("\n", $output));
      return DrushCommands::EXIT_FAILURE;
    }
    $this->output()->writeln('Assets compiled successfully.');

    $this->output()->writeln('Theme dependencies installed successfully!');
    return DrushCommands::EXIT_SUCCESS;
  }

}
