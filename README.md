# Task Pool for PHP

[![TESTS](https://github.com/wilkques/php-task-pool/actions/workflows/github-ci.yml/badge.svg)](https://github.com/wilkques/php-task-pool/actions/workflows/github-ci.yml)
[![Latest Stable Version](https://poser.pugx.org/wilkques/task-pool/v/stable)](https://packagist.org/packages/wilkques/task-pool)
[![License](https://poser.pugx.org/wilkques/task-pool/license)](https://packagist.org/packages/wilkques/task-pool)


## Installation
````
composer require wilkques/task-pool
````

## How to use
```php
use Wilkques\TaskPool\Contracts\TaskContract;
use Wilkques\TaskPool\Exceptions\ForkRunTimeException;

class TaskOne implements TaskContract
{
    public function handle()
    {
        // ... do something
    }
    
    public function resolved($result, $index)
    {
        // ... do something
    }
    
    public function rejected(ForkRunTimeException $forkRunTimeException)
    {
        // ... do something
    }
}

class TaskTwo implements TaskContract
{
    public function handle()
    {
        // ... do something
    }
    
    public function resolved($result, $index)
    {
        // ... do something
    }
    
    public function rejected(ForkRunTimeException $forkRunTimeException)
    {
        // ... do something
    }
}

class TaskThree implements TaskContract
{
    public function handle()
    {
        // ... do something
    }
    
    public function resolved($result, $index)
    {
        // ... do something
    }
    
    public function rejected(ForkRunTimeException $forkRunTimeException)
    {
        // ... do something

        return 
    }
}

$taskOne = new TaskOne;

$taskTwo = new TaskTwo;

$taskThree = new TaskThree;

$taskPool = new TaskPool;

$taskPool->task($taskOne)->task($taskTwo)->task($taskThree);

// or

$taskPool->tasks(
    array(
        $taskOne,
        $taskTwo,
        $taskThree,
    )
);

// run handle
$taskPool->handle();

// if has return
$taskPool->results();
```

## Options
`memory` memory size