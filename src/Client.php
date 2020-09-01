<?php
namespace thawkins\TestLinkAPI;

use thawkins\TestLinkAPI\Entities\Attachment;
use thawkins\TestLinkAPI\Entities\BaseEntity;
use thawkins\TestLinkAPI\Entities\Build;
use thawkins\TestLinkAPI\Entities\Keyword;
use thawkins\TestLinkAPI\Entities\TestPlan;
use thawkins\TestLinkAPI\Entities\PlanTestCase;
use thawkins\TestLinkAPI\Entities\Platform;
use thawkins\TestLinkAPI\Entities\TestProject;
use thawkins\TestLinkAPI\Entities\Step;
use thawkins\TestLinkAPI\Entities\TestCase;
use thawkins\TestLinkAPI\Entities\PlanTestCaseExecution;
use thawkins\TestLinkAPI\Entities\PlanTestCaseInstance;
use thawkins\TestLinkAPI\Entities\TestSuite;
use thawkins\TestLinkAPI\Entities\User;
use thawkins\TestLinkAPI\TestLinkAPIException;
use IXR\Client as XMLRPC;

class Client
{
	private $serverURL = null;

	/** @var XMLRPC\Client */
	private $client = null;

	private $apiKey = null;

	/**
	 * Client constructor.
	 * @param string $serverUrl
	 */
	public function __construct($serverUrl)
	{
		$this->client;
		$this->serverURL = $serverUrl;
	}

	/**
	 * Sets API key for current user
	 * @param string $apiKey
	 */
	public function setAPIKey($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * Check connectivity to TestLink API
	 * @return bool
	 */
	public function checkConnectivity()
	{
		try {
			return $this->_makeCall('tl.ping') === 'Hello!';
		} catch (TestLinkAPIException $e) {
			return false;
		}
	}

	/**
	 * Check extensions
	 * @return bool
	 */
	public function checkExtensions()
	{
		try {
			return $this->_makeCall('tl.isExtended');
		} catch (TestLinkAPIException $e) {
			return false;
		}
	}

	/**
	 * Check if $apiKey is valid
	 * @param $apiKey
	 * @return bool
	 */
	public function checkApiKey($apiKey)
	{
		try {
			$response = $this->_makeCall('tl.checkDevKey', ['devKey' => $apiKey]);
		} catch (TestLinkAPIException $e) {
			return false;
		}

		return $response;
	}

	/**
	 * Gets TestLink version
	 * @return string
	 */
	public function getTestLinkVersion()
	{
		return $this->_makeCall('tl.testLinkVersion');
	}

	/**
	 * Gets information about API
	 * @return string
	 */
	public function getAboutApi()
	{
		return $this->_makeCall('tl.about');
	}

	/**
	 * @return TestProject[]
	 */
	public function getProjects()
	{
		$response = $this->_makeSignCall('tl.getProjects');
		$results = [];
		if(is_array($response)) {
			foreach ($response as $project) {
				$results[] = TestProject::createFromArray($this, $project);
			}
		}

		return $results;
	}

	/**
	 * @param $id
	 * @return TestProject|boolean
	 */
	public function getProjectById($id)
	{
		$response = $this->_makeSignCall('tl.getProjectById', ['testprojectid' => $id]);
		if(is_array($response)) {
			return TestProject::createFromArray($this, $response);
		}
		return false;
	}

	/**
	 * @param $id
	 * @return TestPlan[]
	 */
	public function getPlansByProject(TestProject $project)
	{
		$response = $this->_makeSignCall('tl.getProjectTestPlans', ['testprojectid' => $project->id]);
		$results = [];
		if(is_array($response)) {
			foreach ($response as $testPlan) {
				$plan = TestPlan::createFromArray($this, $testPlan);
				$plan->setTestProject($project);
				$results[] = $plan;
			}
		}
		return $results;
	}

	/**
	 * @param $id
	 * @return TestPlan|boolean
	 */
	public function getPlanById($id)
	{
		$response = $this->_makeSignCall('tl.getTestPlanById', ['testplanid' => $id]);
		if(is_array($response)) {
			return TestPlan::createFromArray($this, $response);
		}
		return false;
	}

	/**
	 * @param TestPlan $plan
	 * @return Build[]
	 */
	public function getBuildsByPlan(TestPlan $plan)
	{
		$response = $this->_makeSignCall('tl.getBuildsForTestPlan', ['testplanid' => $plan->id]);

		$results = [];
		if(is_array($response)) {
			foreach ($response as $build) {
				$build = Build::createFromArray($this, $build);
				$build->setTestPlan($plan);
				$results[] = $build;
			}
		}
		return $results;
	}

	/**
	 * @param $id
	 * @param TestPlan $plan
	 * @return Build|bool
	 */
	public function getBuildById($id, TestPlan $plan)
	{
		$response = $this->_makeSignCall('tl.getBuildById', ['testplanid' => $plan->id, 'buildid' => $id]);
		if(is_array($response)) {
			$build = Build::createFromArray($this, $response);
			$build->setTestPlan($plan);
			return $build;
		}

		return false;
	}

	/**
	 * @param TestPlan $plan
	 * @return Platform[]
	 */
	public function getPlatformsByPlan(TestPlan $plan)
	{
		try {
			$response = $this->_makeSignCall('tl.getTestPlanPlatforms', ['testplanid' => $plan->id]);

			$results = [];
			if(is_array($response)) {
				foreach ($response as $plat) {
					$platform = Platform::createFromArray($this, $plat);
					$platform->setTestPlan($plan);
					$results[] = $platform;
				}
			}
			return $results;
		} catch (TestLinkAPIException $e) {
			if($e->getCode() == 3041) {
				return [];
			}
		}
	}

	/**
	 * @param TestPlan $plan
	 * @return mixed
	 */
	public function getExecPerBuildByPlan(TestPlan $plan)
	{
		$response = $this->_makeSignCall('tl.getExecCountersByBuild', ['testplanid' => $plan->id]);

		return $response;
	}


	/**
	 * @param TestPlan $plan
	 * @param bool $withPath
	 * @param Build|null $build
	 * @param Platform|null $platform
	 * @return PlanTestCase[]
	 */
	public function getPlanTestCasesBy(TestPlan $plan, $withPath = true, Build $build = null, Platform $platform = null)
	{
		if(!$build) {
			$build = $this->getLatestBuildByPlan($plan);
		}
		$args = [
			'testplanid' => $plan->id,
			'getstepsinfo' => true,
		];
		if($build) {
			$args['buildid'] = $build->id;
		}

		if($platform) {
			$args['platformid'] = $platform->id;
		}

		$response = $this->_makeSignCall('tl.getTestCasesForTestPlan', $args);
		$results = [];

		if(is_array($response)) {
			foreach ($response as $testCase) {
				$results[] = new PlanTestCase($this, $plan, $build, $testCase);
			}

			if ($withPath) {
				$results = $this->getAllFullPath($results);
			}
		}

		return $results;
	}

	/**
	 * @param int $testCaseId
	 * @return TestCase|bool
	 */
	public function getTestCaseById($testCaseId)
	{
		$args = [
			'testcaseid' => $testCaseId,
		];
		$response = $this->_makeSignCall('tl.getTestCase', $args);

		if(is_array($response) && isset($response[0])) {
			return TestCase::createFromArray($this, $response[0]);
		} else {
			return false;
		}
	}

	/**
	 * @param PlanTestCase $testCase
	 * @return TestCase|bool
	 */
	public function getTestCaseByPlanTestCase(PlanTestCase $testCase)
	{
		$args = [
			'testcaseid' => $testCase->id,
			'version' => (int)$testCase->version,
		];

		$response = $this->_makeSignCall('tl.getTestCase', $args);

		if(is_array($response) && isset($response[0])) {
			return TestCase::createFromArray($this, $response[0]);
		} else {
			return false;
		}
	}

	/**
	 * @param PlanTestCaseInstance $testCaseInstance
	 * @return PlanTestCaseExecution|bool
	 */
	public function getLastExecutionByTestCaseInstance(PlanTestCaseInstance $testCaseInstance)
	{
		if($testCaseInstance->getBuild()) {
			$args = [
				'testcaseid' => $testCaseInstance->getTestCase()->id,
				'testplanid' => $testCaseInstance->getTestCase()->getTestPlan()->id,
				'buildid' => $testCaseInstance->getBuild()->id,
			];

			if ($testCaseInstance->getPlatform()) {
				$args['platformid'] = $testCaseInstance->getPlatform()->id;
			}

			$response = $this->_makeSignCall('tl.getLastExecutionResult', $args);

			if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
				$result = PlanTestCaseExecution::createFromArray($this, $response[0]);
				$result->setTestCaseInstance($testCaseInstance);
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param BaseEntity[] $entities
	 * @return array
	 */
	public function getAllFullPath(array $entities)
	{
		$query = [];
		foreach($entities as $entity) {
			$query[] = (int) $entity->id;
		}
		$response = $this->_makeSignCall('tl.getFullPath', ['nodeid' => $query]);
		foreach($entities as $entity) {
			if(isset($response[$entity->id])) {
				$entity->setFullPath($response[$entity->id]);
			}
		}

		return $entities;
	}

	/**
	 * @param TestPlan $plan
	 * @return Build
	 */
	public function getLatestBuildByPlan(TestPlan $plan)
	{
		$response = $this->_makeSignCall('tl.getLatestBuildForTestPlan', ['testplanid' => $plan->id]);
		$result = Build::createFromArray($this, $response);
		$result->setTestPlan($plan);

		return $result;
	}

	/**
	 * @param TestPlan $plan
	 * @param string $name
	 * @param string $notes
	 * @param bool $active
	 * @param bool $open
	 * @param string $releaseDate
	 * @return int|bool
	 */
	public function createBuild(TestPlan $plan, $name, $notes, $active, $open, $releaseDate)
	{
		$args = [
			'testplanid' => $plan->id,
			'buildname' => $name,
			'buildnotes' => $notes,
			'active' => $active,
			'open' => $open,
			'releasedate' => $releaseDate,
		];
		$response = $this->_makeSignCall('tl.createBuild', $args);

		if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
			return $response[0]['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param TestPlan $plan
	 * @return TestSuite[]
	 */
	public function getTestSuitesByPlan(TestPlan $plan)
	{
		$response = $this->_makeSignCall('tl.getTestSuitesForTestPlan', ['testplanid' => $plan->id]);

		$results = [];

		if(is_array($response)) {
			foreach ($response as $testSuite) {
				$testSuite = TestSuite::createFromArray($this, $testSuite);
				$results[] = $testSuite;
			}
		}
		return $results;
	}

	/**
	 * @param string $name
	 * @param string $prefix
	 * @param string $notes
	 * @param bool $active
	 * @param bool $public
	 * @param bool $optionRequirements
	 * @param bool $optionTestPriority
	 * @param bool $optionAutomation
	 * @param bool $optionInventory
	 * @return int|bool
	 */
	public function createTestProject($name, $prefix, $notes, $active = true, $public = true, $optionRequirements = true, $optionTestPriority = true, $optionAutomation = true, $optionInventory = true)
	{
		$args = [
			'testprojectname' => $name,
			'testcaseprefix' => $prefix,
			'notes' => $notes,
			'active' => $active,
			'public' => $public,
			'options' => [
				'requirementsEnabled' => $optionRequirements,
				'testPriorityEnabled' => $optionTestPriority,
				'automationEnabled' => $optionAutomation,
				'inventoryEnabled' => $optionInventory,
			]
		];
		$response = $this->_makeSignCall('tl.createTestProject', $args);

		if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
			return $response[0]['id'];
		} else {
			return false;
		}
	}


	/**
	 * @param TestSuite $testSuite
	 * @param bool $deep
	 * @param bool $keywords
	 * @return TestCase[]
	 */
	public function getTestCasesByTestSuite(TestSuite $testSuite, $deep = true, $keywords = false)
	{
		$args = [
			'testsuiteid' => $testSuite->id,
			'deep' => $deep,
			'details' => 'full',
			'keywords' => $keywords,
		];

		$response = $this->_makeSignCall('tl.getTestCasesForTestSuite', $args);

		$results = [];

		if(is_array($response)) {
			foreach ($response as $tc) {
				$testCase = TestCase::createFromArray($this, $tc);
				$results[] = $testCase;
			}
		}
		return $results;

	}

	/**
	 * @param TestProject $project
	 * @param TestSuite $testSuite
	 * @param User $user
	 * @param $name
	 * @param $summary
	 * @param array $steps
	 * @param $preconditions
	 * @return int|bool
	 */
	public function createTestCase(TestProject $project, TestSuite $testSuite, User $user, $name, $summary, array $steps, $preconditions)
	{
		$args = [
			'testcasename' => $name,
			'testsuiteid' => $testSuite->id,
			'testprojectid' => $project->id,
			'summary' => $summary,
			'preconditions' => $preconditions,
			'authorlogin' => $user->login,
		];

		foreach($steps as $step) {
			$args['steps'][] = [
				'step_number' => $step->step_number,
				'actions' => $step->actions,
				'expected_results' => $step->expected_results,
				'execution_type' => $step->execution_type,
			];
		}

		$response = $this->_makeSignCall('tl.createTestCase', $args);

		if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
			return $response[0]['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param TestProject $project
	 * @return TestSuite[]
	 */
	public function getFirstLevelTestSuitesByProject(TestProject $project)
	{
		$response = $this->_makeSignCall('tl.getFirstLevelTestSuitesForTestProject', ['testprojectid' => $project->id]);

		$results = [];

		if(is_array($response)) {
			foreach ($response as $ts) {
				$testSuite = TestSuite::createFromArray($this, $ts);
				$results[] = $testSuite;
			}
		}
		return $results;

	}

	/**
	 * @param TestCase $testCase
	 * @return Attachment[]
	 */
	public function getAttachmentsByTestCase(TestCase $testCase)
	{
		$response = $this->_makeSignCall('tl.getTestCaseAttachments', ['testcaseid' => $testCase->testcase_id]);

		$results = [];

		if(is_array($response)) {
			foreach ($response as $attach) {
				$attachment = Attachment::createFromArray($this, $attach);
				$results[] = $attachment;
			}
		}
		return $results;

	}

	/**
	 * @param TestProject $project
	 * @param TestSuite $testSuite
	 * @param $name
	 * @param $details
	 * @param TestSuite|null $parent
	 * @return int|bool
	 */
	public function updateTestSuite(TestProject $project, TestSuite $testSuite, $name, $details, TestSuite $parent = null)
	{
		$args = [
			'testsuiteid' => $testSuite->id,
			'testprojectid' => $project->id,
			'testsuitename' => $name,
			'details' => $details,
		];

		if($parent) {
			$args['parentid'] = $parent->id;
		}

		$response = $this->_makeSignCall('tl.updateTestSuite', $args);

		if (is_array($response) && isset($response[0]) && isset($response[0]['status'])) {
			return $response[0]['status'];
		} else {
			return false;
		}
	}

	/**
	 * @param TestProject $project
	 * @param string $name
	 * @param string $details
	 * @param TestSuite|null $parent
	 * @return int|bool
	 */
	public function createTestSuite(TestProject $project, $name, $details, TestSuite $parent = null)
	{
		$args = [
			'testprojectid' => $project->id,
			'testsuitename' => $name,
			'details' => $details,
		];

		if($parent) {
			$args['parentid'] = $parent->id;
		}

		$response = $this->_makeSignCall('tl.createTestSuite', $args);

		if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
			return $response[0]['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param TestProject $project
	 * @param string $name
	 * @param string $notes
	 * @param bool $active
	 * @param bool $public
	 * @return int|bool
	 */
	public function createTestPlan(TestProject $project, $name, $notes, $active = true, $public = true)
	{
		$args = [
			'prefix' => $project->prefix,
			'testplanname' => $name,
			'notes' => $notes,
			'active' => $active,
			'public' => $public,
		];

		$response = $this->_makeSignCall('tl.createTestPlan', $args);


		if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
			return $response[0]['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param PlanTestCaseExecution $testCaseExecution
	 * @return int|bool
	 */
	public function deleteExecution(PlanTestCaseExecution $testCaseExecution)
	{
		$args = [
			'executionid' => (int)$testCaseExecution->id,
		];

		$response = $this->_makeSignCall('tl.deleteExecution', $args);


		if (is_array($response) && isset($response[0]) && $response[0]['id'] != -1) {
			return $response[0]['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param int $id
	 * @return TestSuite
	 */
	public function getTestSuiteById($id)
	{
		$response = $this->_makeSignCall('tl.getTestSuiteByID', ['testsuiteid' => $id]);

		$result = TestSuite::createFromArray($this, $response);
		return $result;

	}

	/**
	 * @param TestSuite $testSuite
	 * @return TestSuite[]
	 */
	public function getTestSuitesByTestSuite(TestSuite $testSuite)
	{
		$response = $this->_makeSignCall('tl.getTestSuitesForTestSuite', ['testsuiteid' => $testSuite->id]);

		$results = [];


		if(is_array($response) && !isset($response['id'])) {
			foreach ($response as $ts) {
				$testSuite = TestSuite::createFromArray($this, $ts);
				$results[] = $testSuite;
			}
		} elseif (is_array($response) && isset($response['id'])){
			$results[] = TestSuite::createFromArray($this, $response);
		}
		return $results;
	}

	/**
	 * @param TestPlan $plan
	 * @return mixed
	 */
	public function getTotalsByPlan(TestPlan $plan)
	{
		$response = $this->_makeSignCall('tl.getTotalsForTestPlan', ['testplanid' => $plan->id]);

		return $response;

	}

	/**
	 * @param string $username
	 * @return bool
	 */
	public function doesUserExist($username)
	{
		$response = $this->_makeSignCall('tl.doesUserExist', ['user' => $username]);

		return $response;
	}

	/**
	 * @param TestProject $project
	 * @param string $filename
	 * @param string $filetype
	 * @param string $content
	 * @param string|null $title
	 * @param string|null $description
	 * @return mixed
	 */
	public function uploadTestProjectAttachment(TestProject $project, $filename, $filetype, $content, $title = null, $description = null)
	{
		$args = [
			'testprojectid' => $project->id,
			'filename' => $filename,
			'filetype' => $filetype,
			'content' => $content,
		];

		if($title) {
			$args['title'] = $title;
		}

		if($description) {
			$args['description'] = $description;
		}

		$response = $this->_makeSignCall('tl.uploadTestProjectAttachment', $args);

		return $response;
	}

	/**
	 * @param TestSuite $testSuite
	 * @param string $filename
	 * @param string $filetype
	 * @param string $content
	 * @param string|null $title
	 * @param string|null $description
	 * @return mixed
	 */
	public function uploadTestSuiteAttachment(TestSuite $testSuite, $filename, $filetype, $content, $title = null, $description = null)
	{
		$args = [
			'testsuiteid' => $testSuite->id,
			'filename' => $filename,
			'filetype' => $filetype,
			'content' => $content,
		];

		if($title) {
			$args['title'] = $title;
		}

		if($description) {
			$args['description'] = $description;
		}

		$response = $this->_makeSignCall('tl.uploadTestSuiteAttachment', $args);

		return $response;
	}

	/**
	 * @param TestCase $testCase
	 * @param string $filename
	 * @param string $filetype
	 * @param string $content
	 * @param string|null $title
	 * @param string|null $description
	 * @return mixed
	 */
	public function uploadTestCaseAttachment(TestCase $testCase, $filename, $filetype, $content, $title = null, $description = null)
	{
		$args = [
			'testcaseid' => $testCase->id,
			'filename' => $filename,
			'filetype' => $filetype,
			'content' => $content,
		];

		if($title) {
			$args['title'] = $title;
		}

		if($description) {
			$args['description'] = $description;
		}

		$response = $this->_makeSignCall('tl.uploadTestCaseAttachment', $args);

		return $response;
	}

	/**
	 * @param PlanTestCaseExecution $testCaseExecution
	 * @param string $filename
	 * @param string $filetype
	 * @param string $content
	 * @param string|null $title
	 * @param string|null $description
	 * @return mixed
	 */
	public function uploadExecutionAttachment(PlanTestCaseExecution $testCaseExecution, $filename, $filetype, $content, $title = null, $description = null)
	{
		$args = [
			'executionid' => $testCaseExecution->id,
			'filename' => $filename,
			'filetype' => $filetype,
			'content' => $content,
		];

		if($title) {
			$args['title'] = $title;
		}

		if($description) {
			$args['description'] = $description;
		}

		$response = $this->_makeSignCall('tl.uploadExecutionAttachment', $args);

		return $response;
	}

	/**
	 * @param TestCase $testCase
	 * @param Step[] $steps
	 * @param int|null $version
	 * @param string $action
	 * @return mixed
	 */
	public function createTestCaseSteps(TestCase $testCase, array $steps, $version = null, $action = 'create')
	{
		$args = [
			'testcaseid' => $testCase->testcase_id,
			'action' => $action,
		];

		if($version) {
			$args['version'] = $version;
		}

		foreach($steps as $step) {
			$args['steps'][] = [
				'step_number' => $step->step_number,
				'actions' => $step->actions,
				'expected_results' => $step->expected_results,
				'execution_type' => $step->execution_type,
			];
		}

		$response = $this->_makeSignCall('tl.createTestCaseSteps', $args);

		return true;

	}

	/**
	 * @param TestCase $testCase
	 * @param Step[] $steps
	 * @param int|null $version
	 * @return mixed
	 */
	public function deleteTestCaseSteps(TestCase $testCase, array $steps, $version = null)
	{
		$args = [
			'testcaseid' => $testCase->testcase_id,
		];

		if($version) {
			$args['version'] = $version;
		}

		foreach($steps as $step) {
			$args['steps'][] = $step->step_number;
		}

		$response = $this->_makeSignCall('tl.deleteTestCaseSteps', $args);

		return true;
	}

	/**
	 * @param TestProject $project
	 * @param string $name
	 * @param string $notes
	 * @return int|bool
	 */
	public function createPlatform(TestProject $project, $name, $notes)
	{
		$args = [
			'testprojectname' => $project->name,
			'platformname' => $name,
			'notes' => $notes,
		];

		$response = $this->_makeSignCall('tl.createPlatform', $args);

		if (is_array($response) && $response['id'] != -1) {
			return $response['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param TestProject $project
	 * @return Platform[]
	 */
	public function getPlatformsByProject(TestProject $project)
	{
		try {
			$response = $this->_makeSignCall('tl.getProjectPlatforms', ['testprojectid' => $project->id]);

			$results = [];
			if (is_array($response)) {
				foreach ($response as $plat) {
					$platform = Platform::createFromArray($this, $plat);
					$results[] = $platform;
				}
			}
			return $results;
		} catch (TestLinkAPIException $e) {
			if($e->getCode() == 3041) {
				return [];
			}
		}
	}

	/**
	 * @param TestPlan $plan
	 * @param Platform $platform
	 * @return bool
	 */
	public function assignPlatformToPlan(TestPlan $plan, Platform $platform)
	{
		$args = [
			'testplanid' => $plan->id,
			'platformname' => $platform->name,
		];

		$response = $this->_makeSignCall('tl.addPlatformToTestPlan', $args);

		return true;
	}

	/**
	 * @param TestPlan $plan
	 * @param Platform $platform
	 * @return bool
	 */
	public function removePlatformFromPlan(TestPlan $plan, Platform $platform)
	{
		$args = [
			'testplanid' => $plan->id,
			'platformname' => $platform->name,
		];

		$response = $this->_makeSignCall('tl.removePlatformFromTestPlan', $args);

		return true;
	}

	/**
	 * @param string $login
	 * @return User|bool
	 */
	public function getUserByLogin($login)
	{
		$args = [
			'user' => $login,
		];

		$response = $this->_makeSignCall('tl.getUserByLogin', $args);

		if(isset($response[0])) {
			return User::createFromArray($this, $response[0]);
		}

		return false;
	}

	/**
	 * @param $id
	 * @return User|bool
	 */
	public function getUserById($id)
	{
		$args = [
			'userid' => $id,
		];

		$response = $this->_makeSignCall('tl.getUserByID', $args);

		if(isset($response[0])) {
			return User::createFromArray($this, $response[0]);
		}

		return false;
	}

	/**
	 * @param TestCase $testCase
	 * @param int|null $version
	 * @param string|null $name
	 * @param string|null $summary
	 * @param Step[] $steps
	 * @param string|null $preconditions
	 * @return mixed
	 */
	public function updateTestCase(TestCase $testCase, $version = null, $name = null, $summary = null, array $steps = [], $preconditions = null)
	{
		$args = [
			'testcaseexternalid' => $testCase->full_tc_external_id,
		];

		if($version) {
			$args['version'] = $version;
		}

		if($name) {
			$args['name'] = $name;
		}

		if($summary) {
			$args['summary'] = $summary;
		}

		if($preconditions) {
			$args['preconditions'] = $preconditions;
		}

		if($steps) {
			foreach ($steps as $step) {
				$args['steps'][] = [
					'step_number' => $step->step_number,
					'actions' => $step->actions,
					'expected_results' => $step->expected_results,
					'execution_type' => $step->execution_type,
				];
			}
		}

		$response = $this->_makeSignCall('tl.updateTestCase', $args);

		return isset($response['status_ok'])?$response['status_ok']:false;
	}

	/**
	 * @param User $user
	 * @param PlanTestCaseInstance $testCaseInstance
	 * @return mixed
	 */
	public function assignTestCaseInstanceToUser(User $user, PlanTestCaseInstance $testCaseInstance)
	{
		$args = [
			'user' => $user->login,
			'testplanid' => $testCaseInstance->getBuild()->testplan_id,
			'testcaseexternalid' => $testCaseInstance->getTestCase()->getTestCase()->full_tc_external_id,
			'buildid' => $testCaseInstance->getBuild()->id,
		];

		if($testCaseInstance->getPlatform()) {
			$args['platformid'] = $testCaseInstance->getPlatform()->id;
		}

		$response = $this->_makeSignCall('tl.assignTestCaseExecutionTask', $args);

		return isset($response['status'])?$response['status']:false;
	}

	/**
	 * @param User|null $user
	 * @param PlanTestCaseInstance $testCaseInstance
	 * @return mixed
	 */
	public function removeTestCaseInstanceFromUser(User $user = null, PlanTestCaseInstance $testCaseInstance)
	{
		$args = [
			'testplanid' => $testCaseInstance->getBuild()->testplan_id,
			'testcaseexternalid' => $testCaseInstance->getTestCase()->getTestCase()->full_tc_external_id,
			'buildid' => $testCaseInstance->getBuild()->id,
		];

		if($testCaseInstance->getPlatform()) {
			$args['platformid'] = $testCaseInstance->getPlatform()->id;
		}

		if($user) {
			$args['user'] = $user->login;
		} else {
			$args['action'] = 'unassignAll';
		}

		$response = $this->_makeSignCall('tl.unassignTestCaseExecutionTask', $args);

		return isset($response['status'])?$response['status']:false;
	}

	/**
	 * @param PlanTestCaseInstance $testCaseInstance
	 * @return User|bool
	 */
	public function getUserByAssignedTestCaseInstance(PlanTestCaseInstance $testCaseInstance)
	{
		$args = [
			'testplanid' => $testCaseInstance->getBuild()->testplan_id,
			'testcaseexternalid' => $testCaseInstance->getTestCase()->getTestCase()->full_tc_external_id,
			'buildid' => $testCaseInstance->getBuild()->id,
		];

		if($testCaseInstance->getPlatform()) {
			$args['platformid'] = $testCaseInstance->getPlatform()->id;
		}

		$response = $this->_makeSignCall('tl.getTestCaseAssignedTester', $args);


		if(isset($response[0])) {
			return User::createFromArray($this, $response[0]);
		}

		return false;
	}

	/**
	 * @param TestProject $project
	 * @return Keyword[]
	 */
	public function getKeywordsByProject(TestProject $project)
	{
		$args = [
			'testprojectid' => $project->id,
		];

		$response = $this->_makeSignCall('tl.getProjectKeywords', $args);

		$results = [];

		if(is_array($response)) {
			foreach ($response as $kw) {
				$keyword = Keyword::createFromArray($this, ['name' => $kw]);
				$results[] = $keyword;
			}
		}

		return $results;
	}

	/**
	 * @param TestCase $testCase
	 * @return Keyword[]
	 */
	public function getKeywordsByTestCase(TestCase $testCase)
	{

		$args = [
			'testcaseid' => $testCase->testcase_id,
		];

		$response = $this->_makeSignCall('tl.getTestCaseKeywords', $args);

		$results = [];

		if(is_array($response) && isset($response[$testCase->testcase_id])) {
			foreach($response[$testCase->testcase_id] as $kw) {
				$keyword = Keyword::createFromArray($this, ['name' => $kw]);
				$results[] = $keyword;
			}
		}

		return $results;
	}

	/**
	 * @param TestPlan $plan
	 * @return mixed
	 */
	public function deleteTestPlan(TestPlan $plan)
	{
		$args = [
			'testplanid' => $plan->id,
		];

		$response = $this->_makeSignCall('tl.deleteTestPlan', $args);

		return isset($response[0]['status'])?$response[0]['status']:false;
	}

	/**
	 * @param TestCase $testCase
	 * @param Keyword[] $keywords
	 * @return bool
	 */
	public function assignKeywordsToTestCase(TestCase $testCase, array $keywords)
	{
		$args['keywords'] = [];
		foreach ($keywords as $keyword) {
			$args['keywords'][$testCase->full_tc_external_id][] = $keyword->name;
		}

		$response = $this->_makeSignCall('tl.addTestCaseKeywords', $args);

		return isset($response['status_ok'])?$response['status_ok']:false;
	}

	/**
	 * @param TestCase $testCase
	 * @param Keyword[] $keywords
	 * @return bool
	 */
	public function removeKeywordsFromTestCase(TestCase $testCase, array $keywords)
	{
		$args['keywords'] = [];
		foreach ($keywords as $keyword) {
			$args['keywords'][$testCase->full_tc_external_id][] = $keyword->name;
		}

		$response = $this->_makeSignCall('tl.removeTestCaseKeywords', $args);

		return isset($response['status_ok'])?$response['status_ok']:false;
	}

	/**
	 * @param TestProject $project
	 * @return mixed
	 */
	public function deleteTestProject(TestProject $project)
	{
		$args = [
			'prefix' => $project->prefix,
		];

		$response = $this->_makeSignCall('tl.deleteTestProject', $args);

		return isset($response[0]['status'])?$response[0]['status']:false;
	}

	/**
	 * @param int $testCaseId
	 * @param string $status
	 * @param string $notes
	 * @param int $testPlanId
	 * @param int $buildId
	 * @param int|null $platformId
	 * @param array|null $steps
	 * @return mixed
	 */
	public function reportExecution($testCaseId, $status, $notes, $testPlanId, $buildId, $platformId = null, array $steps = null)
	{
		$args = [
			'testcaseid' => $testCaseId,
			'status' => $status,
			'notes' => $notes,
			'testplanid' => $testPlanId,
			'buildid' => $buildId,
		];
		if($platformId) {
			$args['platformid'] = $platformId;
		}

		if($steps) {
			$args['steps'] = $steps;
		}

		$response = $this->_makeSignCall('tl.reportTCResult', $args);
		return $response[0]['status'];
	}

	/**
	 * @param string $method
	 * @param array|null $args
	 * @return mixed
	 */
	protected function _makeCall($method, $args = null)
	{
		$this->getClient()->query($method, $args);
		if($this->getClient()->isError()) {
			throw new TestLinkAPIException($this->getClient()->getErrorMessage(), $this->getClient()->getErrorCode());
		}
		return $this->checkResponse($this->getClient()->getResponse());
	}

	/**
	 * @param string $method
	 * @param array|null $args
	 * @return mixed
	 */
	protected function _makeSignCall($method, $args = null)
	{
		$args['devKey'] = $this->apiKey;
		return $this->_makeCall($method, $args);
	}

	protected function checkResponse($response)
	{
		if(is_array($response)) {
			foreach($response as $res) {
				if (isset($res['code']) && $res['code'] != null) {
					throw new TestLinkAPIException($res['message'], $res['code']);
				} elseif (isset($res['status_ok']) && $res['status_ok'] != null && $res['status_ok'] == 0) {
					throw new TestLinkAPIException($res['msg'], $res['status_ok']);
				}
			}
		}

		return $response;
	}

	public function setClient($client)
	{
		$this->client = $client;
	}

	/**
	 * @return XMLRPC\Client
	 */
	protected function getClient()
	{
		if(!$this->client) {
			$this->client = new XMLRPC\Client($this->serverURL);
		}
		return $this->client;
	}
}