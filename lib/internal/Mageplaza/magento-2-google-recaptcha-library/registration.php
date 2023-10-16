<?php

/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GoogleRecaptchaLib
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

class Recaptcha_Autoload
{
	public static function autoload($class){
		if (substr($class, 0, 10) !== 'ReCaptcha\\') {
		  /* If the class does not lie under the "ReCaptcha" namespace,
		   * then we can exit immediately.
		   */
		  return;
		}

		/* All of the classes have names like "ReCaptcha\Foo", so we need
		 * to replace the backslashes with frontslashes if we want the
		 * name to map directly to a location in the filesystem.
		 */
		$class = str_replace('\\', '/', $class);

		/* First, check under the current directory. It is important that
		 * we look here first, so that we don't waste time searching for
		 * test classes in the common case.
		 */
		$path = dirname(__FILE__).'/'.$class.'.php';
		if (is_readable($path)) {
			require_once $path;

			return;
		}

		/* If we didn't find what we're looking for already, maybe it's
		 * a test class?
		 */
		$path = dirname(__FILE__).'/../tests/'.$class.'.php';
		if (is_readable($path)) {
			require_once $path;
		}
	}
}
spl_autoload_register(['Recaptcha_Autoload', 'autoload']);
