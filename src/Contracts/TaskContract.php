<?php

namespace Wilkques\TaskPool\Contracts;

interface TaskContract
{
    /**
     * Execution successful.
     * 
     * @param mixed $result
     * @param string|int $index
     * 
     * @return mixed
     */
    public function resolved($result, $index);

    /**
     * Execution failed.
     * 
     * @param \Wilkques\TaskPool\Exceptions\ForkRunTimeException $forkRunTimeException
     * 
     * @return \Exception
     */
    public function rejected($forkRunTimeException);

    /**
     * Running...
     * 
     * @return mixed|void
     */
    public function handle();
}