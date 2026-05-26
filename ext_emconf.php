<?php

$_EXTKEY = 'gedankenfolger_viewhelper';

$EM_CONF[$_EXTKEY] = [
    'title' => 'Gedankenfolger Viewhelper',
    'description' => 'A collection of viewhelpers to make the work a little bit easier',
    'category' => 'misc',
    'author' => 'Niels Tiedt',
    'author_email' => 'niels.tiedt@gedankenfolger.de',
    'author_company' => 'Gedankenfolger GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '14.0.8',
    'autoload' => [
        'psr-4' => [
            'Gedankenfolger\\GedankenfolgerViewhelper\\' => 'Classes',
        ],
    ],
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
