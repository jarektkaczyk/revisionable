# Sofa/Revisionable

Nice and easy way to handle revisions of your db.

Full docs coming soon...


## Requirements

* This package requires PHP 5.4+
* Currently it works out of the box with Laravel4 + generic Auth guard OR cartalyst/sentry 2


## Usage (Laravel4 example)

### 1. Download the package or require in your `composer.json`:

```
    "require": {
        ...
        "sofa/revisionable": "*",
        ...
    },

```

### 2. Add the service provider to your `app/config/app.php`:

```
    'providers' => array(

        ...

        'Sofa\Revisionable\Laravel4\RevisionableServiceProvider',
    ),
```

### 3. Publish the package config file:

```
~$ php artisan config:publish sofa/revisionable
```

this will create `app/config/packages/sofa/revisionable/config.php` file, where you can adjust a few settings:

```
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User provider (auth) implementation.
    |--------------------------------------------------------------------------
    |
    | By default Laravel4 generic Illuminate\Auth\Guard.
    |
    | Supported options:
    |  - illuminate
    |  - sentry
    */
    'userprovider' => 'illuminate',


    /*
    |--------------------------------------------------------------------------
    | Table used for the revisions.
    |--------------------------------------------------------------------------
    */
    'table'        => 'revisions',


    /*
    |--------------------------------------------------------------------------
    | Templates for the Presenter
    |--------------------------------------------------------------------------
    */
    'templates'    => [

        /*
        |----------------------------------------------------------------------
        | Template for the renderDiff method
        |----------------------------------------------------------------------
        */
        'diff'    => [
            'start' => '<div>',
            'body'  => '<p class="diff-string">'
                            .'<span class="diff-key">:key</span>: '
                            .'<span class="diff-old">:old</span>&nbsp;&rarr;&nbsp;<span class="diff-new">:new</span>'
                        .'</p>',
            'end'   => '</div>',
        ],
    ],
];
```

* `userprovider` is the authentication driver you are using. Currently supported are generic Laravel4 Guard and Cartalyst/Sentry 2. It will provide the logger with user identifier.
* `table` for logging the revisions
* `templates` are there for the Presenter, which is rendering revisions diff

### 4. Run the migration in order to create the revisions table:

```
~$ php artisan migrate --package=sofa/revisionable [--database=your_connection]
```

You can provide additional `--database` param if you want the migration to be run using non-default db connection.

### 5. Add revisionable trait to the models you wish to keep track of and set the properties as needed:

```
<?php

use Sofa\Revisionable\Laravel4\RevisionableTrait;

class User extends Eloquent {

    // use the trait
    use RevisionableTrait;

    // Set revisionable whitelist - only changes to any
    // of these fields will be tracked during updates.
    protected $revisionable = [
        'email',
        'name',
    ];

    // Or revisionable blacklist - if $revisionable is not set
    // then you can exclude some fields from being tracked.
    protected $nonRevisionable = [
        'created_at',
        'updated_at',
    ];

    // Note: if you don't specify any of the above properties, Revisionable 
    // will exclude created_at, updated_at, deleted_at fields by default.

    
    // Connection to use by Revisionable.
    // By default logger will use default connection, so if you use
    // another one for given model (eg. different DB), 
    // feel free to set it appropriately.
    protected $revisionableConnection = 'custom_connection';

```


Et voila!