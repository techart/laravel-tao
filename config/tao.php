<?php return [

    'routers' => [
        'fspages' => \TAO\FSPages\Router::class,
        'admin' => \TAO\Admin\Router::class,
        'models' => \TAO\Fields\Router::class,
    ],
    
    'fields' => [
        'string' => \TAO\Fields\Type\StringField::class,
        'remember_token' => \TAO\Fields\Type\RememberToken::class,
        'date_integer' => \TAO\Fields\Type\DateInteger::class,
        'integer' => \TAO\Fields\Type\Integer::class,
        'text' => \TAO\Fields\Type\Text::class,
        'checkbox' => \TAO\Fields\Type\Checkbox::class,
        'password' => \TAO\Fields\Type\Password::class,
        'multilink' => \TAO\Fields\Type\Multilink::class,
        'select' => \TAO\Fields\Type\Select::class,
        'upload' => \TAO\Fields\Type\Upload::class,
        'image' => \TAO\Fields\Type\Image::class,
    ],
    
    'datatypes' => [
        'users' => \TAO\Fields\Model\User::class,
        'roles' => \TAO\Fields\Model\Role::class,
    ],
    
];