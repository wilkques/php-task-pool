<?php

namespace Wilkques\TaskPool\Tests;

use PHPUnit\Framework\TestCase;
use Wilkques\TaskPool\Contracts\TaskContract;
use Wilkques\TaskPool\TaskPool;

class TaskPoolTests extends TestCase
{
    /** @var TaskPool */
    protected $taskPool;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $taskMockOne;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $taskMockTwo;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $taskMockThree;

    public function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    private function init()
    {
        $this->taskPool = new TaskPool;

        $this->taskMockOne = $this->getMockBuilder(TaskContract::class)->getMock();

        $this->taskMockTwo = $this->getMockBuilder(TaskContract::class)->getMock();

        $this->taskMockThree = $this->getMockBuilder(TaskContract::class)->getMock();

        $this->taskMockOne->method('handle')->willReturn('task1');

        $this->taskMockTwo->method('handle')->willReturn('task2');

        $this->taskMockThree->method('handle')->willReturn('task3');

        $this->taskMockOne->method('resolved')->willReturnCallback(function ($result, $index) {
            return $result;
        });

        $this->taskMockTwo->method('resolved')->willReturnCallback(function ($result, $index) {
            return $result;
        });

        $this->taskMockThree->method('resolved')->willReturnCallback(function ($result, $index) {
            return $result;
        });
    }

    public function testHandle()
    {
        $this->taskPool->task($this->taskMockOne)->task($this->taskMockTwo)->task($this->taskMockThree)->handle();

        $this->assertEquals(
            array(
                'task1',
                'task2',
                'task3',
            ),
            $this->taskPool->results()
        );
    }

    public function testHandleWithKey()
    {
        $this->taskPool->task($this->taskMockOne, 'one')
            ->task($this->taskMockTwo, 'two')
            ->task($this->taskMockThree, 'three')
            ->handle();

        $this->assertEquals(
            array(
                'one'   => 'task1',
                'two'   => 'task2',
                'three' => 'task3',
            ),
            $this->taskPool->results()
        );
    }

    public function testHandleTasks()
    {
        $this->taskPool->task($this->taskMockOne)->task($this->taskMockTwo)->task($this->taskMockThree);

        $this->taskPool->tasks(
            array(
                $this->taskMockOne,
                $this->taskMockTwo,
                $this->taskMockThree,
            )
        )->handle();

        $this->assertEquals([
            'task1',
            'task2',
            'task3',
        ], $this->taskPool->results());
    }

    public function testHandleTasksWithKey()
    {
        $this->taskPool->tasks(
            array(
                'one'   => $this->taskMockOne,
                'two'   => $this->taskMockTwo,
                'three' => $this->taskMockThree,
            )
        )->handle();

        $this->assertEquals(array(
            'one'   => 'task1',
            'two'   => 'task2',
            'three' => 'task3',
        ), $this->taskPool->results());
    }
}
