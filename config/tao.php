<?php return [

    'routers' => [
        'fspages' => \TAO\FSPages\Router::class,
        'admin' => \TAO\Admin\Router::class,
        'models' => \TAO\Fields\Router::class,
    ],
    
    'fields' => [
        'dummy' => \TAO\Fields\Type\Dummy::class,
        'string' => \TAO\Fields\Type\StringField::class,
        'remember_token' => \TAO\Fields\Type\RememberToken::class,
        'date_integer' => \TAO\Fields\Type\DateInteger::class,
        'integer' => \TAO\Fields\Type\Integer::class,
        'text' => \TAO\Fields\Type\Text::class,
        'checkbox' => \TAO\Fields\Type\Checkbox::class,
        'password' => \TAO\Fields\Type\Password::class,
        'multilink' => \TAO\Fields\Type\Multilink::class,
        'multilink_tags' => \TAO\Fields\Type\MultilinkTags::class,
        'select' => \TAO\Fields\Type\Select::class,
        'upload' => \TAO\Fields\Type\Upload::class,
        'image' => \TAO\Fields\Type\Image::class,
        'attaches' => \TAO\Fields\Type\Attaches::class,
        'documents' => \TAO\Fields\Type\Documents::class,
        'gallery' => \TAO\Fields\Type\Gallery::class,
    ],

    'text' => [
        'processors' => [
            'markdown' => \TAO\Text\Processor\Parser\Markdown::class,
            'translit' => \TAO\Text\Processor\Translit::class,
            'translit_for_url' => \TAO\Text\Processor\TranslitForUrl::class,
        ]
    ],
    
    'datatypes' => [
        'users' => \TAO\Fields\Model\User::class,
        'roles' => \TAO\Fields\Model\Role::class,
    ],
    
];