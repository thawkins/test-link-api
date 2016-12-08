<?php
namespace Skalda\TestLinkAPI\Entities;

class TestPlan extends BaseEntity
{
	protected $testProject = null;
	protected $builds = null;
	protected $platforms = null;

	public $id;
	public $name;
	public $notes;
	public $active;
	public $is_public;
	public $testproject_id;

	public function getTestProject()
	{
		if($this->testProject === null) {
			$this->testProject = $this->client->getProjectById($this->testproject_id);
		}
		return $this->testProject;
	}

	public function setTestProject(TestProject $project)
	{
		$this->testProject = $project;
	}

	public function getBuilds()
	{
		if($this->builds === null) {
			$this->builds = $this->client->getBuildsByPlan($this);
		}
		return $this->builds;
	}

	public function fillBuildsWithExecInfo()
	{
		$this->client->getExecPerBuildByPlan($this);
	}

	public function getPlatforms()
	{
		if($this->platforms === null) {
			$this->platforms = $this->client->getPlatformsByPlan($this);
		}
		return $this->platforms;
	}

}