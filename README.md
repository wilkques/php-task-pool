# Task Pool for PHP

[![TESTS](https://github.com/wilkques/php-task-pool/actions/workflows/github-cd.yml/badge.svg)](https://github.com/wilkques/php-task-pool/actions/workflows/github-ci.yml)
[![Latest Stable Version](https://poser.pugx.org/wilkques/task-pool/v/stable)](https://packagist.org/packages/wilkques/task-pool)
[![License](https://poser.pugx.org/wilkques/task-pool/license)](https://packagist.org/packages/wilkques/task-pool)


## Installation
````
composer require wilkques/task-pool
````

## How to use
```php
class TaskOne implements TaskContract
{
    public function handle()
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
}

class TaskThree implements TaskContract
{
    public function handle()
    {
        // ... do something
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

