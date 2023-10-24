<?php

namespace LaravelCommon\App\Console\Commands;

use Illuminate\Console\Command;
use LaravelCommon\App\Models\LoggingConfig;

class CreateLoggingName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:data:logging
        {name : logging name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create logging config data name';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Command is running...");

        $name = $this->argument('name');
        $loggingConfig = new LoggingConfig();
        $loggingConfig->setName($name);

        $this->info("$name name is created");
        return 0;
    }
}
