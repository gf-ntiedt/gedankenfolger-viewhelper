<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Gedankenfolger Viewhelper',
    'description' => 'A collection of viewhelpers to make the work a little bit easier',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Gedankenfolger\\GedankenfolgerViewhelper\\' => 'Classes',
        ],
    ],
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Niels Tiedt',
    'author_email' => 'niels.tiedt@gedankenfolger.de',
    'author_company' => 'Gedankenfolger',
    'version' => '13.1.0',
];
