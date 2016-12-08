<?php
namespace Skalda\TestLinkAPI\Entities;

class PlanTestCaseInstance extends BaseEntity
{
	const STATUS_FAIL = "f";
	const STATUS_SUCCESS= "p";
	const STATUS_BLOCK = "b";
	const STATUS_INCOMPLETE = "n";

	/** @var Platform */
	protected $platform;

	/** @var Build */
	protected $build;

	/** @var PlanTestCase */
	protected $testCase;
	protected $lastExecution;

	public $featureId;
	public $platformId;
	public $platformName;
	public $execStatus;
	public $executionDuration;
	public $execId;
	public $tcversionId;
	public $execOnBuild;
	public $execOnTPlan;

	public function getTestCase()
	{
		return $this->testCase;
	}

	public function setTestCase(PlanTestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	public function getPlatform()
	{
		return $this->platform;
	}

	public function setPlatform(Platform $platform)
	{
		$this->platform = $platform;
	}

	public function getBuild()
	{
		return $this->build;
	}

	public function setBuild(Build $build)
	{
		$this->build = $build;
	}

	public function getLastExecution()
	{
		if($this->lastExecution === null) {
			$this->lastExecution = $this->client->getLastExecutionByTestCaseInstance($this);
		}

		return $this->lastExecution;
	}

}