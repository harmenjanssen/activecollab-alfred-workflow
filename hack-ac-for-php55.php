<?php
/**
 * Alright, this is dumb.
 * The ActiveCollab lib uses rest arguments, which is a PHP 5.6 feature.
 * OSX El Capitan is shipped with PHP 5.5, and even though I run PHP 7 on my development machine,
 * the `php` binary used by Alfred resolves to the native one. This will result in a syntax error
 * when the script is ran from Alfred.
 *
 * This script goes in and modifies the source files of the API so I can use it on OSX without
 * having to instruct non-technical folks to upgrade their PHP.
 * The SDK version is locked in composer.json so it should be fine, albeit... unorthodox.
 *
 * @author Harmen Janssen <harmen@whatstyle.net>
 */

define('AC_PACKAGE_ROOT', dirname(__FILE__) . '/vendor/activecollab/activecollab-feather-sdk');

// First, remove `...` from the AuthenticatorInterface
$interfacePath = AC_PACKAGE_ROOT . '/src/AuthenticatorInterface.php';
$interface = file_get_contents($interfacePath);
$interface = str_replace('...', '', $interface);
file_put_contents($interfacePath, $interface);

// Second, remove `...` from the SelfHosted subclass
// It doesn't actually use the rest params, so this one's easy
$selfHostedPath = AC_PACKAGE_ROOT . '/src/Authenticator/SelfHosted.php';
$selfHosted = file_get_contents($selfHostedPath);
$selfHosted = str_replace('...', '', $selfHosted);
file_put_contents($selfHostedPath, $selfHosted);

// Third, modify Cloud.php, which actually uses them, so that's the "difficult" one.
// But honestly, only `$arguments[0]` is in use, so we can just swap it out for a regular param.
$cloudPath = AC_PACKAGE_ROOT . '/src/Authenticator/Cloud.php';
$cloud = file_get_contents($cloudPath);
$cloud = str_replace('...', '', $cloud);
$cloud = str_replace('arguments[0]', 'arguments', $cloud);
file_put_contents($cloudPath, $cloud);
