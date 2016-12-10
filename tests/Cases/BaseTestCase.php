<?php
/**
 * Created by PhpStorm.
 * User: Skalda
 * Date: 09/12/16
 * Time: 21:35
 */

namespace Skalda\TestLinkAPI\Tests\Cases;


use Skalda\TestLinkAPI\Client;
use Skalda\TestLinkAPI\Entities\Build;
use Skalda\TestLinkAPI\Entities\Keyword;
use Skalda\TestLinkAPI\Entities\PlanTestCase;
use Skalda\TestLinkAPI\Entities\PlanTestCaseExecution;
use Skalda\TestLinkAPI\Entities\PlanTestCaseInstance;
use Skalda\TestLinkAPI\Entities\Platform;
use Skalda\TestLinkAPI\Entities\TestPlan;
use Skalda\TestLinkAPI\Entities\TestProject;
use Skalda\TestLinkAPI\Entities\TestSuite;
use Skalda\TestLinkAPI\Entities\User;
use Skalda\TestLinkAPI\Tests\Mockup\ClientMock;
use Tester\TestCase;

abstract class BaseTestCase extends TestCase
{
	/** @var Client */
	protected $testLinkAPI;
	/** @var ClientMock */
	protected $clientMock;

	public function setUp()
	{
		$this->clientMock = new ClientMock();
		$this->testLinkAPI = new Client('http://testlink.cz');
		$this->testLinkAPI->setClient($this->clientMock);
	}

	protected function mockResponse($filename)
	{
		$this->clientMock->mockResponse(__DIR__ . '/../Files/XML/' . $filename);
	}

	protected function getTestProject()
	{
		$project = new TestProject($this->testLinkAPI);
		$project->id = 1;
		$project->notes = 'Project notes';
		$project->color = 'Project color';
		$project->active = true;
		$project->option_reqs = true;
		$project->option_priority = false;
		$project->option_automation = false;
		$project->options = [];
		$project->prefix = 'PRE';
		$project->tc_counter = 12;
		$project->is_public = true;
		$project->issue_tracker_enabled = false;
		$project->reqmgr_integration_enabled =false;
		$project->api_key = null;
		$project->name = 'Project name';
		$project->opt = [];

		return $project;
	}

	protected function getTestPlan()
	{
		$plan = new TestPlan($this->testLinkAPI);
		$plan->id = 2;
		$plan->name = 'Plan name';
		$plan->notes = 'Plan notes';
		$plan->active = true;
		$plan->is_public = true;
		$plan->testproject_id = 1;
		$plan->platforms[] = $this->getPlatform();
		return $plan;
	}

	protected function getBuild()
	{
		$build = new Build($this->testLinkAPI);
		$build->id = 3;
		$build->testplan_id = 2;
		$build->name = 'Build name';
		$build->notes = 'Build notes';
		$build->active = true;
		$build->is_open = true;
		$build->release_date = '2017-01-01';
		$build->closed_on_date = '2017-01-03';
		$build->creation_ts = '2016-10-20';

		return $build;
	}

	protected function getPlatform()
	{
		$platform = new Platform($this->testLinkAPI);
		$platform->id = 4;
		$platform->name = 'Platform name';
		$platform->notes = 'Platform notes';

		return $platform;
	}

	protected function getTestSuite()
	{
		$testSuite = new TestSuite($this->testLinkAPI);
		$testSuite->id = 5;
		$testSuite->name = 'Suite name';
		$testSuite->parent_id = 1;

		return $testSuite;
	}

	protected function getKeyword()
	{
		$keyword = new Keyword($this->testLinkAPI);
		$keyword->name = 'Keyword name';

		return $keyword;
	}

	protected function getTestCase()
	{
		$testCase = new \Skalda\TestLinkAPI\Entities\TestCase($this->testLinkAPI);
		$testCase->updater_login = 'admin';
		$testCase->author_login = 'admin';
		$testCase->name = 'Case name';
		$testCase->node_order = 1;
		$testCase->testsuite_id = 5;
		$testCase->testcase_id = 6;
		$testCase->id = 6;
		$testCase->tc_external_id = 'PRE';
		$testCase->version = 1;
		$testCase->layout = null;
		$testCase->status = 'approved';
		$testCase->summary = 'Case summary';
		$testCase->preconditions = 'Case preconditions';
		$testCase->importance = 'important';
		$testCase->author_id = 10;
		$testCase->creation_ts = '2016-10-20';
		$testCase->updater_id = 10;
		$testCase->modification_ts = '2016-11-20';
		$testCase->active = true;
		$testCase->is_open = true;
		$testCase->execution_type = 'manual';
		$testCase->estimated_exec_duration = '20';
		$testCase->author_first_name = 'AdminFirst';
		$testCase->author_last_name = 'AdminLast';
		$testCase->updater_first_name = 'AdminFirst';
		$testCase->updater_last_name = 'AdminLast';
		$testCase->steps = [];
		$testCase->full_tc_external_id = 'PRE6';

		return $testCase;
	}

	protected function getPlanTestCase()
	{
		$planTestCase = new PlanTestCase($this->testLinkAPI, $this->getTestPlan(), $this->getBuild(), [
			4 => [
				'feature_id' => '6',
				'platform_id' => '4',
				'platform_name' => 'Platform name',
				'exec_status' => 'p',
				'execution_duration' => '20',
				'exec_id' => 9,
				'tcversion_number' => 1,
				'exec_on_build' => 3,
				'exec_on_tplan' => 2,
				'tcase_id' => 6,
				'tcase_name' => 'Case name',
				'tcversion_id' => 6,
				'version' => 1,
				'external_id' => 'PRE',
				'execution_type' => 'manual',
				'status' => 'approved',
				'execution_order' => '1',
				'full_external_id' => 'PRE6',
			]
		]);

		$planTestCase->setTestCase($this->getTestCase());

		return $planTestCase;
	}

	/**
	 * @return PlanTestCaseInstance
	 */
	protected function getPlanTestCaseInstance()
	{
		return $this->getPlanTestCase()->getInstances()[0];
	}

	protected function getPlanTestCaseExecution()
	{
		$execution = new PlanTestCaseExecution($this->testLinkAPI);
		$execution->id = 9;
		$execution->build_id = 3;
		$execution->tester_id = '11';
		$execution->execution_ts = '2016-11-20';
		$execution->status = 'p';
		$execution->testplan_id = 2;
		$execution->tcversion_id = 6;
		$execution->tcversion_number = 1;
		$execution->platform_id = 4;
		$execution->execution_type = 'manual';
		$execution->execution_duration = '30';
		$execution->notes = 'Execution notes';
		$execution->setTestCaseInstance($this->getPlanTestCaseInstance());

		return $execution;
	}

	protected function getUser()
	{
		$user = new User($this->testLinkAPI);
		$user->firstName = 'adminFirst';
		$user->lastName = 'adminLast';
		$user->emailAddress = 'admin@admin.cz';
		$user->locale = 'cs';
		$user->isActive = true;
		$user->defaultTestprojectID = 1;
		$user->login = 'admin';
		$user->dbID = 11;

		return $user;
	}
}