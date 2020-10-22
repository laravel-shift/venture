<?php declare(strict_types=1);

namespace Sassnowski\Venture\Manager;

use Sassnowski\Venture\Models\Workflow;
use PHPUnit\Framework\Assert as PHPUnit;
use Sassnowski\Venture\AbstractWorkflow;

class WorkflowManagerFake implements WorkflowManagerInterface
{
    private array $started = [];

    public function startWorkflow(AbstractWorkflow $abstractWorkflow): Workflow
    {
        $pendingWorkflow = $abstractWorkflow->definition();

        [$workflow, $initialBatch] = $pendingWorkflow->build();

        $this->started[get_class($abstractWorkflow)] = $abstractWorkflow;

        return $workflow;
    }

    public function hasStarted(string $workflowClass, ?callable $callback = null): bool
    {
        if (!array_key_exists($workflowClass, $this->started)) {
            return false;
        }

        if ($callback === null) {
            return true;
        }

        return $callback($this->started[$workflowClass]);
    }

    public function assertStarted(string $workflowDefinition, ?callable $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->hasStarted($workflowDefinition, $callback),
            "The expected workflow [{$workflowDefinition}] was not started."
        );
    }
}
