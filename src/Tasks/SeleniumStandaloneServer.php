<?php
/**
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Testing\Robo\Tasks;

use Joomla\Testing\Robo\Handlers\RoboHandler;

/**
 * Class for executing and stopping Selenium Standalone Server
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @since       1.0.0
 *
 * @todo        Add Windows compatibility
 */
final class SeleniumStandaloneServer extends GenericTask
{
	/**
	 * Human readable name of the task
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected $taskName = "Selenium Server";

	/**
	 * The domain and port to selenium hub site
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $url = 'http://localhost:4444';

	/**
	 * Binary of the selenium standalone server
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $binary = 'vendor/bin/selenium-server-standalone';

	/**
	 * Include debug option in the params or not
	 *
	 * @var bool
	 *
	 * @since 1.0.0
	 */
	private $debug = true;

	/**
	 * Optionally add a catch-all log file
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $logFile = 'selenium.log';

	/**
	 * Number of seconds to wait for the server to start
	 *
	 * @var int
	 *
	 * @since 1.0.0
	 */
	private $timeOut = 60;

	/**
	 * Set Selenium URL
	 *
	 * @param   string  $url  URL of selenium
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setURL($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Set Selenium Binary
	 *
	 * @param   string  $binary  Selenium Standalone Server binary
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setBinary($binary)
	{
		$this->binary = $binary;

		return $this;
	}

	/**
	 * Set debug option for Selenium server
	 *
	 * @param   bool  $debug  Add debug or not
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setDebug($debug = true)
	{
		$this->debug = $debug;

		return $this;
	}

	/**
	 * Set a location for a catch-all log
	 *
	 * @param   string  $logFile  File to store the log in
	 *
	 * @return $this
	 *
	 * @since  1.0.0
	 */
	public function setLogFile($logFile)
	{
		$this->logFile = $logFile;

		return $this;
	}

	/**
	 * Set a timeout for the Selenium Standalone Server
	 *
	 * @param   int  $seconds  Number of seconds to wait for the process to start
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;

		return $this;
	}

	/**
	 * Sets a task for running the selenium server
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function runSelenium()
	{
		return $this->setupTask('runSelenium');
	}

	/**
	 * Sets a task to wait for the server to be ready
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function waitForSelenium()
	{
		return $this->setupTask('waitForSelenium');
	}

	/**
	 * Sets a task to kill the selenium server
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function killSelenium()
	{
		return $this->setupTask('killSelenium');
	}

	/**
	 * Start the Selenium Server with the given options
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function _runSelenium()
	{
		$roboHandler = RoboHandler::getInstance();

		$this->printTaskInfo('Executing Selenium Standalone server');

		// If no binary given, it doesn't start up the server
		if (empty($this->binary) || !file_exists($this->binary))
		{
			$this->printTaskError('No Selenium binary provided for execution');

			return false;
		}

		$command = $this->binary;
		$command .= (($this->debug) ? ' -debug' : '');
		$command .= (!empty($this->logFile)) ? ' >> ' . $this->logFile . ' 2>&1' : '';

		if (!$roboHandler->executeDaemon($command))
		{
			$this->printTaskError('Selenium server execution failed');

			return false;
		}

		return true;
	}

	/**
	 * Wait for server to be available
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function _waitForSelenium()
	{
		$this->printTaskInfo('Waiting for Selenium Standalone server to launch...');
		$timeout = 0;

		if (!$this->isWindows())
		{
			while (!$this->isUrlAvailable($this->url . '/wd/hub'))
			{
				// If selenium has not started after the given number of seconds then die
				if ($timeout > $this->timeOut)
				{
					$this->printTaskError('Selenium server execution failed (timeout expired)');

					return false;
				}

				sleep(1);
				$timeout++;
			}
		}
		else
		{
			sleep(10);
		}

		$this->printTaskSuccess('Selenium server is executing correctly');

		return true;
	}

	/**
	 * Kills the server in execution
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function _killSelenium()
	{
		$roboHandler = RoboHandler::getInstance();
		$this->printTaskInfo('Stopping Selenium Server');

		$command = 'curl ' . $this->url . '/selenium-server/driver/?cmd=shutDownSeleniumServer';

		if ($roboHandler->executeCommand($command))
		{
			$this->printTaskSuccess('Selenium stopped successfully');

			return true;
		}

		$this->printTaskError('Selenium failed to be stopped');

		return false;
	}

	/**
	 * Checks if the given URL is available
	 *
	 * @param   string  $url  URL to check
	 *
	 * @return bool
	 *
	 * @since   1.0.0
	 */
	private function isUrlAvailable($url)
	{
		$roboHandler = RoboHandler::getInstance();
		$command = 'curl  --retry 6 --retry-delay 10 --output /dev/null --silent ' . $url;

		if ($roboHandler->executeCommand($command))
		{
			return true;
		}

		return false;
	}
}
