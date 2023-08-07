# Task Pool for PHP

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

