<?php

declare(strict_types=1);

use In2code\Luxletter\Domain\Model\Category;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Model\Usergroup;

return [
    User::class => [
        'tableName' => 'fe_users',
    ],
    Usergroup::class => [
        'tableName' => 'fe_groups',
    ],
    Category::class => [
        'tableName' => Category::TABLE_NAME,
    ],
];
