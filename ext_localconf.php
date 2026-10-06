<?php

defined('TYPO3') or die('Access denied.');

// TYPO3 13 does not load Configuration/Fluid/Namespaces.php (TYPO3 14 only),
// so the global gfv namespace has to be registered here as well.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['gfv'][] =
    'Gedankenfolger\\GedankenfolgerViewhelper\\ViewHelpers';
