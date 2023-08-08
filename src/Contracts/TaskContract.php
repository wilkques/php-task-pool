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
     * @return \Exception
     */
    public function rejected();

    /**
     * Running...
     * 
     * @return mixed|void
     */
    public function handle();
}