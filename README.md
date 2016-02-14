# laravel-cruser
Create users for your laravel project from the command line.

## requirements
Laravel 5

## install
**Copy/download** 
the `src/CreateUser.php` file to
`path/to/laravel-app/app/Console/Commands`

**Register** 
the class within your applications Console Kernel Class
`app/Console/Kernel.php`
```php
    protected $commands = [
        // add here
        Commands\CreateUser::class,
    ];
```
**NOTE**: If you don't want the command to be available within your production environment you can add the command within a constructor function (instead of adding the class directly to the `$commands` variable).
```php
    public function __construct(Application $app, Dispatcher $events) {
        parent::__construct($app,$events);

        $app->booted(function () use ($app){
            if (!$app->environment('production')) {
                $this->commands[] = Commands\CreateUser::class;
            }
        });
    }
```
**Change namespace**
If you have changed the name of your Application you will need to modify the namespace of the CreateUser Class accordingly.

## good to go
The CreateUser Command acts as template, just extend / modify the code to suit your needs. Out of the box it works with the users table that is created by laravels default users migration.

## usage
Pass email and name of the user as arguments, you will be prompted for the password.
`php artisan db:create-user email@example.com "Jane Doe"`

Without arguments, you will be prompted for email and password.
`php artisan db:create-user`
