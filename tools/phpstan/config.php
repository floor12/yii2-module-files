<?php

// Application config for PHPStan: the package registers itself as the "files" module

return [
    'modules' => [
        'files' => [
            'class' => floor12\files\Module::class,
        ],
    ],
];
