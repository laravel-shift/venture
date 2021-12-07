<?php

declare(strict_types=1);

/**
 * Copyright (c) 2021 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ksassnowski/venture
 */

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Sassnowski\Venture\JobExtractor;
use Sassnowski\Venture\Manager\WorkflowManager;
use Sassnowski\Venture\UnserializeJobExtractor;
use Stubs\TestJob1;
use Stubs\TestJob2;

uses(TestCase::class);

it('can handle old workflows that still saved serialized dependent jobs instead of step ids', function (): void {
    Bus::fake();

    $workflow = createWorkflow([
        'job_count' => 2,
        'jobs_processed' => 0,
    ]);
    $job1 = (new TestJob1())->withStepId(Str::orderedUuid());
    $job2 = (new TestJob2())->withStepId(Str::orderedUuid());
    $job1->dependantJobs = [$job2];
    $job2->withDependencies([TestJob1::class]);
    $workflow->addJobs(wrapJobsForWorkflow([$job1, $job2]));

    $workflow->onStepFinished($job1);

    Bus::assertDispatched(TestJob2::class);
});

it('can handle missing workflow step id generator class in config', function (): void {
    config([
        'venture' => [
            'workflow_table' => 'workflows',
            'jobs_table' => 'workflow_jobs',
        ],
    ]);

    expect(app('venture.manager'))->toBeInstanceOf(WorkflowManager::class);
});

it('can handle missing job extractor class in config', function (): void {
    config([
        'venture' => [
            'workflow_table' => 'workflows',
            'jobs_table' => 'workflow_jobs',
        ],
    ]);

    expect(app(JobExtractor::class))->toBeInstanceOf(UnserializeJobExtractor::class);
});
