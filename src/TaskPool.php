<?php

namespace Wilkques\TaskPool;

use Wilkques\TaskPool\Exceptions\ForkRunTimeException;

class TaskPool
{
    /** @var array */
    private $tasks;

    /** @var resource|false|Shmop */
    private $sharedMemoryId;

    /** @var int */
    private $sharedMemorySize;

    /** @var resource|false|SysvSemaphore */
    private $semaphoreId;

    /** @var array */
    protected $options;

    /** @var array */
    protected $results;

    /**
     * TaskPool constructor.
     * 
     * @param array $tasks The list of tasks to be executed
     * @param array $options
     */
    public function __construct($tasks = array(), $options = array())
    {
        $this->boot($tasks, $options);
    }

    /**
     * @param array $tasks
     * @param array $options
     */
    private function boot($tasks = array(), $options = array())
    {
        $this->tasks($tasks)->setOptions(
            array_merge(array(
                'rejected'  => function (ForkRunTimeException $exception) {
                    return $exception;
                },
                'resolved'  => function ($result, $index) {
                    return $result;
                },
                'memory'    => 1024, // Allocate 1KB of shared memory space for each task
            ), $options)
        );
    }

    /**
     * @param array $options
     * 
     * @return static
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return static
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * 
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return data_get($this->getOptions(), $key, $default);
    }

    /**
     * @param int $memory
     * 
     * @return int
     */
    public function memory($memory = 1024)
    {
        return $this->getOption('memory', $memory);
    }

    /**
     * @param int $memorySize
     * 
     * @return static
     */
    public function sharedMemorySize($memorySize = 1024)
    {
        $this->sharedMemorySize = count($this->tasks) * $this->memory($memorySize);

        return $this;
    }

    /**
     * @return int
     */
    public function getSharedMemorySize()
    {
        return $this->sharedMemorySize;
    }

    /**
     * @param string $message
     * 
     * @return ForkRunTimeException
     */
    public function forkRunTimeException($message)
    {
        return new ForkRunTimeException($message);
    }

    /**
     * The shared memory segment
     * 
     * @return static
     */
    private function sharedMemoryId()
    {
        $this->sharedMemoryId = shmop_open(ftok(__FILE__, 't'), 'c', 0644, $this->getSharedMemorySize());

        return $this;
    }

    /**
     * @return resource|false|Shmop
     */
    private function getSharedMemoryId()
    {
        return $this->sharedMemoryId;
    }

    /**
     * The semaphore for synchronization
     * 
     * @return static
     */
    private function semaphoreId()
    {
        $this->semaphoreId = sem_get(ftok(__FILE__, 't'));
        // sem_acquire($this->semaphoreId);

        return $this;
    }

    /**
     * @return resource|false|SysvSemaphore
     */
    private function getSemaphoreId()
    {
        return $this->semaphoreId;
    }

    /**
     * Writes the result to the shared memory at the specified index
     * @param int $index The index of the task
     * @param string $result The result to be written
     * 
     * @return static
     */
    private function writeResultToSharedMemory($index, $result)
    {
        $offset = $index * $this->memory();

        shmop_write($this->getSharedMemoryId(), $result, $offset);

        return $this;
    }

    /**
     * @param array $tasks
     * 
     * @return static
     */
    public function tasks($tasks = array())
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param \Wilkques\TaskPool\Contracts $task
     * @param string|int $index
     * 
     * @return static
     */
    public function task($task, $index = null)
    {
        if (is_null($index)) {
            $this->tasks[] = $task;

            return $this;
        }

        $this->tasks[$index] = $task;

        return $this;
    }

    /**
     * @param string|int $index
     * 
     * @return \Wilkques\TaskPool\Contracts\TaskContract
     */
    public function getTask($index)
    {
        return $this->getTasks()[$index];
    }

    /**
     * Runs the task pool
     * 
     * @return static
     */
    public function handle()
    {
        $processes = array();

        $this->sharedMemorySize($this->memory())->sharedMemoryId()->semaphoreId();

        $i = 0;

        foreach ($this->getTasks() as $task) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                $rejected = $this->getOption('rejected');

                $forkRunTimeException = $this->forkRunTimeException("Unable to fork");

                if (!$rejected or !$rejected instanceof \Exception) {
                    throw $forkRunTimeException;
                }

                throw $rejected($forkRunTimeException);
            }

            if ($pid == 0) {
                $result = $this->executeTask($task);

                sem_acquire($this->getSemaphoreId()); // Acquire the semaphore to ensure synchronization of shared memory access

                $this->writeResultToSharedMemory($i, $result);

                sem_release($this->getSemaphoreId()); // Release the semaphore

                exit(0);
            }

            $processes[$pid] = true;

            $i++;
        }

        while (count($processes) > 0) {
            $status = 0;

            $pid = pcntl_waitpid(-1, $status, WNOHANG);

            if ($pid > 0) {
                unset($processes[$pid]);
            }

            usleep(100000);
        }

        $this->collectResults();

        $this->cleanup();

        return $this;
    }

    /**
     * Executes a task and returns the result
     * @param \Wilkques\TaskPool\Contracts\TaskContract $task The task to be executed
     * 
     * @return mixed The result of the task execution
     */
    private function executeTask($task)
    {
        // Perform the actual task logic here and return the result
        return $task->handle();
    }

    /**
     * Collects and prints the results of the task pool
     */
    private function collectResults()
    {
        $resolved = $this->getOption('resolved');

        $sharedMemorySize = $this->memory();

        $i = 0;

        foreach ($this->getTasks() as $index => $task) {
            $result = shmop_read($this->getSharedMemoryId(), $i * $sharedMemorySize, $sharedMemorySize);

            $result = rtrim($result, "\0");

            $this->setResult($resolved($result, $index), $index);

            $i++;
        }
    }

    /**
     * @param mixed $result
     * @param int $index
     * 
     * @return static
     */
    protected function setResult($result, $index)
    {
        $this->results[$index] = $result;

        return $this;
    }

    /**
     * @return array
     */
    public function results()
    {
        return $this->results;
    }

    /**
     * Cleans up the shared memory and semaphore resources
     */
    private function cleanup()
    {
        shmop_delete($this->getSharedMemoryId());

        shmop_close($this->getSharedMemoryId());

        sem_remove($this->getSemaphoreId());
    }
}