<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\contracts\sproutreports;

/**
 * Class BaseReport
 *
 * @package barrelstrength\sproutbase\contracts\sproutreports
 */
abstract class BaseReport
{
	/**
	 * @return string
	 */
	abstract public function getName();

	/**
	 * @return string
	 */
	abstract public function getHandle();

	/**
	 * @return string
	 */
	abstract public function getGroupName();

	/**
	 * @return string
	 */
	abstract public function getDescription();

	/**
	 * @return array
	 */
	abstract public function getOptions();

	/**
	 * @return BaseDataSource
	 */
	abstract public function getDataSource();
}
