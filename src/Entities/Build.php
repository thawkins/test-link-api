<?php
namespace thawkins\TestLinkAPI\Entities;

class Build extends BaseEntity
{
	protected $testPlan = null;

	public $id;
	public $testplan_id;
	public $name;
	public $notes;
	public $active;
	public $is_open;
	public $release_date;
	public $closed_on_date;
	public $creation_ts;

	public function getTestPlan()
	{
		if($this->testPlan === null) {
			$this->testPlan = $this->client->getPlanById($this->testplan_id);
		}
		return $this->testPlan;
	}

	public function setTestPlan(TestPlan $plan)
	{
		$this->testPlan = $plan;
	}


}