# Sofa/Revisionable

[![Code quality](https://scrutinizer-ci.com/g/jarektkaczyk/revisionable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jarektkaczyk/revisionable) [![Latest Stable Version](https://poser.pugx.org/sofa/revisionable/v/stable.svg)](https://packagist.org/packages/sofa/revisionable) [![Downloads](https://poser.pugx.org/sofa/revisionable/downloads.svg)](https://packagist.org/packages/sofa/revisionable)

Easy and nice way to handle revisions of your db.

## Example display

**Use the `Presenter`:**
```php
// controller
$revisions = DB::table('revisions')
    ->orderBy('revisions.created_at', 'desc')
    ->get();

$revisions = array_map(function ($revision) {
    return App::make('Sofa\Revisionable\Presenter', [(array) $revision]);
}, $revisions);

return $this->view->make('some.view', compact('revisions'));

// view
@foreach($revisions as $revision)
    <tr>
        <td class="revision-timestamp">{{ $revision->created_at }}</td>
        <td class="revision-type">{{ $revision->type }}</td>
        <td class="revision-table_name">{{ $revision->table_name }}</td>
        <td class="revision-row_id">{{ $revision->row_id }}</td>
        <td class="revision-diff"> {{ $revision->renderDiff() }} </td>
        <td class="revision-user">{{ $revision->user_name }}</td>
        <td class="revision-ip">{{ whois_link($revision->ip) }}</td>
    </tr>
@endforeach
```

And you get something like this:

![image](https://cloud.githubusercontent.com/assets/6928818/6411544/ec10125a-be79-11e4-86a2-b477a2e9ac18.png)


**Also you can show history for a model as easily as below:**
```php
// controller
// get revision history for given model and use the presenter
$revisions = $account->revisions->map(function ($revision) {
  return App::make('Sofa\Revisionable\Presenter', [$revision]); 
});

// show all the fields
@foreach ($revisions as $revision )
  @foreach ($revision->getUpdated() as $field) // all updated field 
    <li>
        {{ $revision->user }} changed {{ $field }} 
        from {{ $revision->old($field) }} to {{ $revision->new($field) }}
    </li>
  @endforeach
@endforeach


// or only specified $field that you need:
@foreach ($revisions as $revision ) @if (in_array($field, $revision->getUpdated()))
    <li>
        On {{$revision->created_at}} user {{ $revision->user }} 
        changed {{ $field }} 
        from {{ $revision->old($field) }} to {{ $revision->new($field) }}
    </li>
@endif @endforeach
```

## Why?

There is already pretty popular package [VentureCraft/Revisionable](https://github.com/VentureCraft/revisionable) handling data revisions, so you may ask why bother? So here a few things:

* VentureCraft's package is Eloquent-specific
* It doesn't let you use custom `boot` method on your revisionable models (can be fixed easily, but it is not currently)
* It refers to the models and dynamic data (foreign keys)
* It handles revisions per each field


*This package takes different approach to the revisions and offers:*

* **Working with Eloquent out of the box**, but not Eloquent-specific
* **Doesn't clash** with Eloquent `boot` method
* It refers to **static data** (what it is supposed to do if we're talking about any kind of logs) - **tables** instead of models, static **values** instead of  foreign keys
* Handles the revisions in **bulk** - one entry covers all the created/updated fields, what makes it really **easy to eg. compare 2 given versions** or get all the data changed during single action. 

Disclaimer: This package is not a fork, nor does it use any piece of code from the linked VentureCraft's one.


## Requirements

* This package requires PHP 5.4+
* Currently it works out of the box with Laravel4 + generic Auth guard OR cartalyst/sentry 2/sentinel 2

## Upgrade from 0.* to 0.2

With Laravel5 realease every package supporting the framework is required to change the way it is registered. 
With this change, I decided to move all the Laravel specific code under `Laravel` namespace and that breaks BC. However upgrade path is very quick and easy, so if you used the package with `Laravel4` this is what you need to do:

```
1. Change the provider entry in your app/config/app.php file to:
'Sofa\Revisionable\Laravel\FourServiceProvider',

2. Change the use statements in your revisionable models to:
use Sofa\Revisionable\Laravel\RevisionableTrait;
```

And it's done!.



## Usage (Laravel5 example)

### 1. Download the package or require in your `composer.json`:

```
    "require": {
        ...
        "sofa/revisionable": "~0.2@dev",
        ...
    },

```

### 2. Add the service provider to your `app/config/app.php`:

```php
    'providers' => array(

        ...

        'Sofa\Revisionable\Laravel\FiveServiceProvider',
    ),
```

### 3. Publish the package config file:

```
~$ php artisan vendor:publish [--provider="Sofa\Revisionable\Laravel\FiveServiceProvider"]
```

this will create `config/sofa_revisionable.php` file, where you can adjust a few settings:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User provider (auth) implementation.
    |--------------------------------------------------------------------------
    |
    | By default Laravel generic Illuminate\Auth\Guard.
    |
    | Supported options:
    |  - illuminate
    |  - sentry
    |  - sentinel
    */
    'userprovider' => 'illuminate',


    /*
    |--------------------------------------------------------------------------
    | User field to be saved as the author of tracked action.
    |--------------------------------------------------------------------------
    |
    | By default:
    |
    |  - id for illuminate
    |  - login field (email) for sentry/sentinel
    */
    'userfield'    => null,


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

* `userprovider` is the authentication driver you are using. Currently supported are generic Laravel Guard and Cartalyst/Sentry 2. It will provide the logger with user identifier.
* `table` for logging the revisions
* `templates` are there for the Presenter, which is rendering revisions diff

### 4. Run the migration in order to create the revisions table:

```
~$ php artisan migrate [--database=custom_connection]
```

You can provide additional `--database` param if you want the migration to be run using non-default db connection.

### 5. Add revisionable trait to the models you wish to keep track of and set the properties as needed:

```php
<?php

use Sofa\Revisionable\Laravel\RevisionableTrait;

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


---

## Usage (Laravel4 example)

### 1. Download the package or require in your `composer.json`:

```
    "require": {
        ...
        "sofa/revisionable": "~0.2@dev",
        ...
    },

```

### 2. Add the service provider to your `app/config/app.php`:

```php
    'providers' => array(

        ...

        'Sofa\Revisionable\Laravel\FourServiceProvider',
    ),
```

### 3. Publish the package config file:

```
~$ php artisan config:publish sofa/revisionable
```

this will create `app/config/packages/sofa/revisionable/config.php` file, where you can adjust a few settings:

```php
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
    |  - sentinel
    */
    'userprovider' => 'illuminate',


    /*
    |--------------------------------------------------------------------------
    | User field to be saved as the author of tracked action.
    |--------------------------------------------------------------------------
    |
    | By default:
    |
    |  - id for illuminate
    |  - login field (email) for sentry/sentinel
    */
    'userfield'    => null,


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

* `userprovider` is the authentication driver you are using. Currently supported are generic Laravel Guard and Cartalyst/Sentry 2. It will provide the logger with user identifier.
* `table` for logging the revisions
* `templates` are there for the Presenter, which is rendering revisions diff

### 4. Run the migration in order to create the revisions table:

```
~$ php artisan migrate --package=sofa/revisionable [--database=your_connection]
```

You can provide additional `--database` param if you want the migration to be run using non-default db connection.

### 5. Add revisionable trait to the models you wish to keep track of and set the properties as needed:

```php
<?php

use Sofa\Revisionable\Laravel\RevisionableTrait;

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
