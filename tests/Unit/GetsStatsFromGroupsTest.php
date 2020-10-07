<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Styde\Enlighten\GetsStatsFromGroups;
use Styde\Enlighten\Models\ExampleGroup;
use Tests\TestCase;

class GetsStatsFromGroupsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_stats_from_an_example_group_collection(): void
    {
        $run = $this->createRun();
        $group = $this->createExampleGroup($run, 'FirstGroupTest');

        $this->createExample($group, 'first_test', 'passed');
        $this->createExample($group, 'second_test', 'passed');
        $this->createExample($group, 'third_test', 'passed');
        $this->createExample($group, 'fourth_test', 'passed');

        $parent = new class {
            use GetsStatsFromGroups;

            public $groups;
        };

        $parent->groups = ExampleGroup::with('stats')->get();

        $this->assertSame(4, $parent->getPassingTestsCount());
        $this->assertSame(4, $parent->getTestsCount());
        $this->assertSame('success', $parent->getStatus());

        $group2 = $this->createExampleGroup($run, 'SecondGroupTest');
        $this->createExample($group2, 'sixth_test', 'skipped');
        $parent->groups = ExampleGroup::with('stats')->get();

        $this->assertSame(4, $parent->getPassingTestsCount());
        $this->assertSame(5, $parent->getTestsCount());
        $this->assertSame('warning', $parent->getStatus());

        $this->createExample($group2, 'fifth_test', 'error');
        $parent->groups = ExampleGroup::with('stats')->get();

        $this->assertSame(4, $parent->getPassingTestsCount());
        $this->assertSame(6, $parent->getTestsCount());
        $this->assertSame('failure', $parent->getStatus());
    }
}