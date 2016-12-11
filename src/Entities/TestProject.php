<?php
namespace Skalda\TestLinkAPI\Entities;

class TestProject extends BaseEntity
{

	protected $testPlans = null;
	protected $platforms = null;
	protected $keywords = null;

	public $id;
	public $notes;
	public $color;
	public $active;
	public $option_reqs;
	public $option_priority;
	public $option_automation;
	public $options;
	public $prefix;
	public $tc_counter;
	public $is_public;
	public $issue_tracker_enabled;
	public $reqmgr_integration_enabled;
	public $api_key;
	public $name;
	public $opt = [];

	public function getTestPlans()
	{
		if($this->testPlans === null) {
			$this->testPlans = $this->client->getPlansByProject($this);
		}
		return $this->testPlans;
	}

	public function getPlatforms()
	{
		if($this->platforms === null) {
			$this->platforms = $this->client->getPlatformsByProject($this);
		}

		return $this->platforms;
	}

	public function getKeywords()
	{
		if($this->keywords === null) {
			$this->keywords = $this->client->getKeywordsByProject($this);
		}

		return $this->keywords;
	}
}