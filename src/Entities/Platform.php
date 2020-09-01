<?php
namespace thawkins\TestLinkAPI\Entities;

class Platform extends BaseEntity
{
	protected $testPlan = null;

	public $id;
	public $name;
	public $notes;

	public function getTestPlan()
	{
		return $this->testPlan;
	}

	public function setTestPlan(TestPlan $plan)
	{
		$this->testPlan = $plan;
	}


}