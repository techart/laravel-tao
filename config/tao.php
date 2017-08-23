<?php return [

    'routers' => [
        'fspages' => \TAO\FSPages\Router::class,
        'admin' => \TAO\Admin\Router::class,
        'models' => \TAO\Fields\Router::class,
    ],
    
    'datatypes' => [
        'users' => \TAO\Fields\Model\User::class,
        'roles' => \TAO\Fields\Model\Role::class,
    ],
    
];