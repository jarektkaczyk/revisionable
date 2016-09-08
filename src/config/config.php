<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User model (for executor relation on Revision model).
    |--------------------------------------------------------------------------
    |
    | By default App\User.
    */
    'usermodel' => 'App\User',

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
    |  - jwt-auth
    |  - session
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
    'userfield' => 'id',


    /*
    |--------------------------------------------------------------------------
    | Table used for the revisions.
    |--------------------------------------------------------------------------
    */
    'table' => 'revisions',


    /*
    |--------------------------------------------------------------------------
    | Database connection used for the revisions.
    |--------------------------------------------------------------------------
    */
    'connection' => null,

];
