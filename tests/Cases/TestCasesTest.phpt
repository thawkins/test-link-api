<?php
namespace Skalda\TestLinkAPI\Tests\Cases;

use Skalda\TestLinkAPI\Entities\Step;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class TestCasesTest extends BaseTestCase
{
	public function testGetTestCaseById()
	{
		$this->mockResponse('tl.getTestCase.xml');

		$testCase = $this->getTestCase();

		$response = $this->testLinkAPI->getTestCaseById($testCase->id);

		Assert::equal('tl.getTestCase', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->id, $this->clientMock->getCalledArgument('testcaseid'));

		Assert::type('Skalda\TestLinkAPI\Entities\TestCase', $response);

		Assert::equal('269', $response->id);

	}

	public function testGetTestCaseByPlanTestCase()
	{
		$this->mockResponse('tl.getTestCase.xml');

		$planTestCase = $this->getPlanTestCase();

		$response = $this->testLinkAPI->getTestCaseByPlanTestCase($planTestCase);

		Assert::equal('tl.getTestCase', $this->clientMock->getCalledMethod());

		Assert::equal($planTestCase->id, $this->clientMock->getCalledArgument('testcaseid'));
		Assert::equal((int)$planTestCase->version, $this->clientMock->getCalledArgument('version'));

		Assert::type('Skalda\TestLinkAPI\Entities\TestCase', $response);

		Assert::equal('269', $response->id);
	}

	public function testGetTestCasesByTestSuite()
	{
		$this->mockResponse('tl.getTestCasesForTestSuite.xml');

		$testSuite = $this->getTestSuite();

		$response = $this->testLinkAPI->getTestCasesByTestSuite($testSuite);

		Assert::equal('tl.getTestCasesForTestSuite', $this->clientMock->getCalledMethod());

		Assert::equal($testSuite->id, $this->clientMock->getCalledArgument('testsuiteid'));
		Assert::true($this->clientMock->getCalledArgument('deep'));
		Assert::false($this->clientMock->getCalledArgument('keywords'));

		Assert::count(4, $response);

		foreach($response as $res) {
			Assert::type('Skalda\TestLinkAPI\Entities\TestCase', $res);
		}
		Assert::equal('393', $response[0]->id);
		Assert::equal('4', $response[1]->id);
		Assert::equal('6', $response[2]->id);
		Assert::equal('8', $response[3]->id);
	}

	public function testCreateTestCase()
	{
		$this->mockResponse('tl.createTestCase.xml');

		$testProject = $this->getTestProject();
		$testSuite = $this->getTestSuite();
		$user = $this->getUser();

		$step[0] = new Step($this->testLinkAPI);
		$step[0]->step_number = 1;
		$step[0]->actions = 'first step';
		$step[1] = new Step($this->testLinkAPI);
		$step[1]->step_number = 2;
		$step[1]->actions = 'second step';

		$response = $this->testLinkAPI->createTestCase($testProject, $testSuite, $user, 'Name', 'Summary', $step, 'Preconditions');

		Assert::equal('tl.createTestCase', $this->clientMock->getCalledMethod());

		Assert::equal('Name', $this->clientMock->getCalledArgument('testcasename'));
		Assert::equal($testSuite->id, $this->clientMock->getCalledArgument('testsuiteid'));
		Assert::equal($testProject->id, $this->clientMock->getCalledArgument('testprojectid'));
		Assert::equal('Summary', $this->clientMock->getCalledArgument('summary'));
		Assert::equal($step[0]->step_number, $this->clientMock->getCalledArgument('steps')[0]['step_number']);
		Assert::equal($step[1]->step_number, $this->clientMock->getCalledArgument('steps')[1]['step_number']);
		Assert::equal('Preconditions', $this->clientMock->getCalledArgument('preconditions'));
		Assert::equal($user->login, $this->clientMock->getCalledArgument('authorlogin'));

		Assert::equal(393, $response);
	}

	public function testGetAttachmentsByTestCase()
	{
		$this->mockResponse('tl.getTestCaseAttachments.xml');

		$testCase = $this->getTestCase();

		$response = $this->testLinkAPI->getAttachmentsByTestCase($testCase);

		Assert::equal('tl.getTestCaseAttachments', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->id, $this->clientMock->getCalledArgument('testcaseid'));

		Assert::count(2, $response);

		foreach($response as $res) {
			Assert::type('Skalda\TestLinkAPI\Entities\Attachment', $res);
		}

		Assert::equal('3', $response[0]->id);
		Assert::equal('2', $response[1]->id);
	}

	public function testUploadTestCaseAttachment()
	{
		$this->mockResponse('tl.uploadAttachment.xml');

		$testCase = $this->getTestCase();

		$response = $this->testLinkAPI->uploadTestCaseAttachment($testCase, 'filename.txt', 'test/plain', 'eW8h');

		Assert::equal('tl.uploadTestCaseAttachment', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->id, $this->clientMock->getCalledArgument('testcaseid'));
		Assert::equal('filename.txt', $this->clientMock->getCalledArgument('filename'));
		Assert::equal('test/plain', $this->clientMock->getCalledArgument('filetype'));
		Assert::equal('eW8h', $this->clientMock->getCalledArgument('content'));

		Assert::type('array', $response);

		Assert::equal('filename.txt', $response['file_name']);
	}

	public function testUpdateTestCase()
	{
		$this->mockResponse('tl.updateTestCase.xml');

		$testProject = $this->getTestProject();
		$testSuite = $this->getTestSuite();
		$testCase = $this->getTestCase();
		$user = $this->getUser();

		$step[0] = new Step($this->testLinkAPI);
		$step[0]->step_number = 1;
		$step[0]->actions = 'first step';
		$step[1] = new Step($this->testLinkAPI);
		$step[1]->step_number = 2;
		$step[1]->actions = 'second step';

		$response = $this->testLinkAPI->updateTestCase($testCase, 1, 'Name', 'Summary', $step, 'Preconditions');

		Assert::equal('tl.updateTestCase', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->full_tc_external_id, $this->clientMock->getCalledArgument('testcaseexternalid'));
		Assert::equal(1, $this->clientMock->getCalledArgument('version'));
		Assert::equal('Name', $this->clientMock->getCalledArgument('name'));
		Assert::equal('Summary', $this->clientMock->getCalledArgument('summary'));
		Assert::equal($step[0]->step_number, $this->clientMock->getCalledArgument('steps')[0]['step_number']);
		Assert::equal($step[1]->step_number, $this->clientMock->getCalledArgument('steps')[1]['step_number']);
		Assert::equal('Preconditions', $this->clientMock->getCalledArgument('preconditions'));

		Assert::true($response);
	}

	public function testAssignTestCaseInstanceToUser()
	{
		$this->mockResponse('tl.assignTestCaseExecutionTask.xml');

		$user = $this->getUser();
		$planTestCaseInstance = $this->getPlanTestCaseInstance();

		$response = $this->testLinkAPI->assignTestCaseInstanceToUser($user, $planTestCaseInstance);

		Assert::equal('tl.assignTestCaseExecutionTask', $this->clientMock->getCalledMethod());

		Assert::equal($user->login, $this->clientMock->getCalledArgument('user'));
		Assert::equal($planTestCaseInstance->getBuild()->testplan_id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($planTestCaseInstance->getTestCase()->getTestCase()->full_tc_external_id, $this->clientMock->getCalledArgument('testcaseexternalid'));
		Assert::equal($planTestCaseInstance->getBuild()->id, $this->clientMock->getCalledArgument('buildid'));
		Assert::equal($planTestCaseInstance->getPlatform()->id, $this->clientMock->getCalledArgument('platformid'));

		Assert::true($response);
	}

	public function testRemoveTestCaseInstanceFromUser()
	{
		$this->mockResponse('tl.unassignTestCaseExecutionTask.xml');

		$user = $this->getUser();
		$planTestCaseInstance = $this->getPlanTestCaseInstance();

		$response = $this->testLinkAPI->removeTestCaseInstanceFromUser($user, $planTestCaseInstance);

		Assert::equal('tl.unassignTestCaseExecutionTask', $this->clientMock->getCalledMethod());

		Assert::equal($user->login, $this->clientMock->getCalledArgument('user'));
		Assert::equal($planTestCaseInstance->getBuild()->testplan_id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($planTestCaseInstance->getTestCase()->getTestCase()->full_tc_external_id, $this->clientMock->getCalledArgument('testcaseexternalid'));
		Assert::equal($planTestCaseInstance->getBuild()->id, $this->clientMock->getCalledArgument('buildid'));
		Assert::equal($planTestCaseInstance->getPlatform()->id, $this->clientMock->getCalledArgument('platformid'));

		Assert::true($response);
	}

	public function testGetPlanTestCaseBy()
	{
		$this->mockResponse('tl.getTestCasesForTestPlan.xml');

		$testPlan = $this->getTestPlan();
		$build = $this->getBuild();
		$platform = $this->getPlatform();

		$response = $this->testLinkAPI->getPlanTestCasesBy($testPlan, false, $build, $platform);

		Assert::equal('tl.getTestCasesForTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($build->id, $this->clientMock->getCalledArgument('buildid'));
		Assert::equal($platform->id, $this->clientMock->getCalledArgument('platformid'));

		Assert::count(4, $response);

		foreach($response as $res) {
			Assert::type('Skalda\TestLinkAPI\Entities\PlanTestCase', $res);
		}

		Assert::equal('268', $response[0]->id);
		Assert::equal('275', $response[1]->id);
		Assert::equal('282', $response[2]->id);
		Assert::equal('289', $response[3]->id);
	}
}

$test = new TestCasesTest();
$test->run();