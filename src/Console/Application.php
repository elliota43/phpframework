<?php
namespace Framework\Console;

class Application
{
    protected array $commands = [];

    public function __construct()
    {
        $this->commands = [
            'serve' => new ServeCommand(),
            'make:controller' => new MakeControllerCommand()
        ];

    }

    public function run(array $argv): void
    {
        $commandName = $argv[1] ?? null;

        if (!$commandName || !isset($this->commands[$commandName])) {
            $this->displayAvailableCommands();
            return;
        }

        $command = $this->commands[$commandName];

        // pass args (argv[2] and onward)
        $command->setArguments(array_slice($argv, 2));

        $command->handle();

        $this->commands[$commandName]->handle();
    }

    public function displayAvailableCommands(): void
    {
        echo "Available commands:\n";
        foreach ($this->commands as $name => $cmd) {
            echo " $name\n";
        }
    }
}