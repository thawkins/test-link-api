<?php
namespace Skalda\TestLinkAPI\Entities;

use Skalda\TestLinkAPI\Client;

class PlanTestCase extends BaseEntity
{

	protected $instances = [];
	protected $testPlan;
	protected $testCase;
	protected $fullPath = [];

	public $id;
	public $name;
	public $versionId;
	public $version;
	public $externalId;
	public $executionType;
	public $status;
	public $executionOrder;
	public $fullExternalId;

	public function __construct(Client $client, TestPlan $plan, Build $build, array $values)
	{
		parent::__construct($client);

		$platforms = [];
		foreach($plan->getPlatforms() as $platform) {
			$platforms[$platform->id] = $platform;
		}

		$tc = null;

		foreach($values as $platformId => $testCase) {
			$instance = new PlanTestCaseInstance($client);
			$instance->featureId = $testCase['feature_id'];
			$instance->platformId = $testCase['platform_id'];
			$instance->platformName = $testCase['platform_name'];
			$instance->execStatus = $testCase['exec_status'];
			$instance->executionDuration = $testCase['execution_duration'];
			$instance->execId = $testCase['exec_id'];
			$instance->tcversionId = $testCase['tcversion_number'];
			$instance->execOnBuild = $testCase['exec_on_build'];
			$instance->execOnTPlan = $testCase['exec_on_tplan'];
			$instance->setTestCase($this);
			$instance->setBuild($build);

			if(is_array($platforms) && count($platforms) > 0) {
				$instance->setPlatform($platforms[$instance->platformId]);
			}

			$this->instances[] = $instance;

			$tc = $testCase;
		}

		if($tc) {
			$this->id = $tc['tcase_id'];
			$this->name = $tc['tcase_name'];
			$this->versionId = $tc['tcversion_id'];
			$this->version = $tc['version'];
			$this->externalId = $tc['external_id'];
			$this->executionType = $tc['execution_type'];
			$this->status = $tc['status'];
			$this->executionOrder = $tc['execution_order'];
			$this->fullExternalId = $tc['full_external_id'];
			$this->testPlan = $plan;
		}
	}

	public function getInstances()
	{
		return $this->instances;
	}

	public function getFullPath()
	{
		if($this->fullPath === null) {
			$this->fullPath = $this->client->getFullPath($this);
		}

		return $this->fullPath;
	}

	public function setFullPath(array $path)
	{
		$this->fullPath = $path;
	}

	public function getTestCase()
	{
		if($this->testCase === null) {
			$this->testCase = $this->client->getTestCaseByPlanTestCase($this);
		}
		return $this->testCase;
	}

	public function getTestPlan()
	{
		return $this->testPlan;
	}


}