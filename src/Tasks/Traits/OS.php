<?php
/**
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Testing\Robo\Tasks\Traits;

use Symfony\Component\Process\Process;

/**
 * Trait for OS-related functions
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @since       1.0.0
 */
trait OS
{
	/**
	 * Get the git executable extension according to Operating System
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getGitExecutableExtension()
	{
		if ($this->isWindows())
		{
			$process = new Process('git.exe --version');
			$process->setTimeout(null);
			$process->run();

			if (0 == $process->getExitCode())
			{
				return '.exe';
			}
		}

		return '';
	}

	/**
	 * Checks if local OS is Windows
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	private function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
}
