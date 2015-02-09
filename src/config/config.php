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
