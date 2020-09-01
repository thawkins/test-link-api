<?php
namespace thawkins\TestLinkAPI\Entities;

class TestSuite extends BaseEntity
{
	protected $fullPath;
	protected $childTestSuites;
	protected $testCases;

	public $name;
	public $id;
	public $parent_id;


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

	public function getChildTestSuites()
	{
		if($this->childTestSuites === null) {
			$this->childTestSuites = $this->client->getTestSuitesByTestSuite($this);
		}

		return $this->childTestSuites;
	}

	public function getTestCases()
	{
		if($this->testCases === null) {
			$this->testCases = $this->client->getTestCasesByTestSuite($this);
		}

		return $this->testCases;
	}

}