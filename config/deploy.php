<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deployment token
    |--------------------------------------------------------------------------
    |
    | The browser-based installer at /deploy is protected by this secret. Set
    | DEPLOY_TOKEN in your .env file to a long random string (for example the
    | output of a password generator) before opening /deploy.
    |
    | Leave it empty to disable the installer completely. Once installation is
    | finished you should clear DEPLOY_TOKEN again so nobody can re-run the
    | migrations or seeders.
    |
    */

    'token' => env('DEPLOY_TOKEN'),

];
