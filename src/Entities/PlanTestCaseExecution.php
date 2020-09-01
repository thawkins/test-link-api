<?php
namespace thawkins\TestLinkAPI\Entities;

class PlanTestCaseExecution extends BaseEntity
{
	protected $testCaseInstance;

	public $id;
	public $build_id;
	public $tester_id;
	public $execution_ts;
	public $status;
	public $testplan_id;
	public $tcversion_id;
	public $tcversion_number;
	public $platform_id;
	public $execution_type;
	public $execution_duration;
	public $notes;

	public function getTestCaseInstance()
	{
		return $this->testCaseInstance;
	}

	public function setTestCaseInstance(PlanTestCaseInstance $testCaseInstance)
	{
		$this->testCaseInstance = $testCaseInstance;
	}
}