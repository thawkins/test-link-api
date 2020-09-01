<?php
namespace thawkins\TestLinkAPI\Tests\Cases;

use thawkins\TestLinkAPI\Entities\Step;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class StepsTest extends BaseTestCase
{
	public function testCreateTestCaseSteps()
	{
		$this->mockResponse('tl.createTestCaseSteps.xml');

		$testCase = $this->getTestCase();
		$step[0] = new Step($this->testLinkAPI);
		$step[0]->step_number = 1;
		$step[0]->actions = 'first step';
		$step[1] = new Step($this->testLinkAPI);
		$step[1]->step_number = 2;
		$step[1]->actions = 'second step';

		$response = $this->testLinkAPI->createTestCaseSteps($testCase, $step, 1);

		Assert::equal('tl.createTestCaseSteps', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->testcase_id, $this->clientMock->getCalledArgument('testcaseid'));
		Assert::equal('create', $this->clientMock->getCalledArgument('action'));
		Assert::equal(1, $this->clientMock->getCalledArgument('version'));
		Assert::equal($step[0]->actions, $this->clientMock->getCalledArgument('steps')[0]['actions']);
		Assert::equal($step[1]->actions, $this->clientMock->getCalledArgument('steps')[1]['actions']);

		Assert::true($response);
	}

	public function testDeleteTestCaseSteps()
	{
		$this->mockResponse('tl.deleteTestCaseSteps.xml');

		$testCase = $this->getTestCase();
		$step[0] = new Step($this->testLinkAPI);
		$step[0]->step_number = 1;
		$step[0]->actions = 'first step';
		$step[1] = new Step($this->testLinkAPI);
		$step[1]->step_number = 2;
		$step[1]->actions = 'second step';

		$response = $this->testLinkAPI->deleteTestCaseSteps($testCase, $step, 1);

		Assert::equal('tl.deleteTestCaseSteps', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->testcase_id, $this->clientMock->getCalledArgument('testcaseid'));
		Assert::equal(1, $this->clientMock->getCalledArgument('version'));
		Assert::equal($step[0]->step_number, $this->clientMock->getCalledArgument('steps')[0]);
		Assert::equal($step[1]->step_number, $this->clientMock->getCalledArgument('steps')[1]);

		Assert::true($response);
	}
}

$test = new StepsTest();
$test->run();