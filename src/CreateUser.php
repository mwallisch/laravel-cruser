<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-user
                            {email?} The new users email address - required
                            {name=John Doe}  The new users name - optional';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user from the command line';

    /**
     * The table the users are stored in
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Flag indicating if messages will be logged to the logfile.
     *
     * @var bool
     */
    protected $logMessages = true;

    /**
     * Validation rules for user input
     * @var array
     */
    protected $validation = [
        'email' => 'required|email',

        // modify to suit your needs
        // for strong/very specific settings use a regular expression
        // @see http://stackoverflow.com/questions/31539727/laravel-password-validation-rule
        // e.g. 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/|confirmed'
        'password' => 'required|min:4|confirmed'
    ];

    /**
     * Create a new command instance.
     *
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        info($this->getName() . ' Command triggered',$this->argument());

        if (!$this->checkTable()) {
            return;
        }

        $email    = $this->emailPrompt($this->argument('email'));
        $password = $this->passwordPrompt();
        $name     = $this->argument('name','John Doe');

        $id = $this->create($email, $password, $name);

        if (!$id) {
            $this->message('User not created!','error');
            return;
        }

        $this->message('User created successfully.');
    }

    /**
     * Prompt to enter the email address until validation passes.
     *
     * @param null $email
     * @return null|string
     */
    protected function emailPrompt($email = null) {
        $email = $email ? $email : $this->ask("Please enter email address for new user");

        $validator = Validator::make(['email' => $email],['email' => $this->validation['email']]);

        if ($validator->fails()) {
            $this->consoleMessage($validator->errors()->first('email'),'error');
            return $this->emailPrompt();
        }

        return $email;
    }

    /**
     * Prompt to enter the password (+ password confirmation) until validation passes.
     *
     * @return mixed
     */
    protected function passwordPrompt() {
        $input = [
            'password' => $this->secret("Please enter password for new user"),
            'password_confirmation' => $this->secret("Please re-enter password")
        ];

        $validator = Validator::make($input, ['password' => $this->validation['password']]);

        if ($validator->fails()) {
            $this->consoleMessage($validator->errors()->first('password'),'error');
            return $this->passwordPrompt();
        }

        return $input['password'];
    }

    /**
     * Create a user record
     *
     * @param $email
     * @param $password
     * @param $name
     *
     * @return int
     */
    protected function create($email, $password, $name) {
        $now = new Carbon;

        $id = DB::table($this->table)->insertGetId([
            'email' => $email,
            'password' => bcrypt($password),
            'name' => $name,
            'created_at'=> $now,
            'updated_at' => $now,
        ]);

        return $id;
    }

    /**
     * Writes a message to the console. Never writes to the logfile.
     *
     * @param $message
     * @param string $type
     */
    protected function consoleMessage($message, $type = 'info') {
        $this->logMessages = 0;
        $this->message($message, $type);
        $this->logMessages = 1;
    }

    /**
     * Write a message to the console.
     * If $this->logMessages is true will also attempt to log the message.
     *
     * @param $message
     * @param string $type
     * @param array $arguments
     */
    protected function message($message, $type = 'info', $arguments = []) {
        $logPrefix = $this->getName() . ' ';

        if (method_exists($this,$type)) {
            $this->{$type}($message);
        }

        if ($this->logMessages) {
            if (function_exists($type)) {
                $type($logPrefix . $message, $arguments);
            }
        }
    }

    /**
     * @return true if the user table exists
     */
    protected function checkTable() {
        if (!$tableExists = Schema::hasTable($this->table)) {
            $this->message("Database Table {$this->table} does not exist.","error");
        }

        return $tableExists;
    }
}
