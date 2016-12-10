<?php
namespace Skalda\TestLinkAPI\Tests\Cases;

use Skalda\TestLinkAPI\Entities\Build;
use Skalda\TestLinkAPI\Entities\PlanTestCase;
use Skalda\TestLinkAPI\Entities\PlanTestCaseInstance;
use Skalda\TestLinkAPI\Entities\TestPlan;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class ExecutionsTest extends BaseTestCase
{

	public function testGetLastExecutionByTestCaseInstance()
	{
		$this->mockResponse('tl.getLastExecutionResult.xml');

		$testCaseInstance = $this->getPlanTestCaseInstance();

		$response = $this->testLinkAPI->getLastExecutionByTestCaseInstance($testCaseInstance);

		Assert::equal('tl.getLastExecutionResult', $this->clientMock->getCalledMethod());

		Assert::equal($testCaseInstance->getBuild()->id, $this->clientMock->getCalledArgument('buildid'));
		Assert::equal($testCaseInstance->getTestCase()->getTestPlan()->id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($testCaseInstance->getTestCase()->id, $this->clientMock->getCalledArgument('testcaseid'));

		Assert::type('Skalda\TestLinkAPI\Entities\PlanTestCaseExecution', $response);

		Assert::equal('3', $response->id);
	}

	public function testDeleteExecution()
	{
		$this->mockResponse('tl.deleteExecution.xml');

		$execution = $this->getPlanTestCaseExecution();

		$response = $this->testLinkAPI->deleteExecution($execution);

		Assert::equal('tl.deleteExecution', $this->clientMock->getCalledMethod());

		Assert::equal($execution->id, $this->clientMock->getCalledArgument('executionid'));

		Assert::equal(9, $response);
	}

	public function testUploadExecutionAttachment()
	{
		$this->mockResponse('tl.uploadAttachment.xml');

		$execution = $this->getPlanTestCaseExecution();

		$response = $this->testLinkAPI->uploadExecutionAttachment($execution, 'filename.txt', 'test/plain', 'eW8h');

		Assert::equal('tl.uploadExecutionAttachment', $this->clientMock->getCalledMethod());

		Assert::equal($execution->id, $this->clientMock->getCalledArgument('executionid'));
		Assert::equal('filename.txt', $this->clientMock->getCalledArgument('filename'));
		Assert::equal('test/plain', $this->clientMock->getCalledArgument('filetype'));
		Assert::equal('eW8h', $this->clientMock->getCalledArgument('content'));

		Assert::type('array', $response);

		Assert::equal('filename.txt', $response['file_name']);
	}

	public function testReportExecution()
	{
		$this->mockResponse('tl.reportTCResult.xml');

		$testCase = $this->getTestCase();
		$testPlan = $this->getTestPlan();
		$build = $this->getBuild();
		$platform = $this->getPlatform();

		$response = $this->testLinkAPI->reportExecution($testCase->id, 'p', 'notes', $testPlan->id, $build->id, $platform->id);

		Assert::equal('tl.reportTCResult', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->id, $this->clientMock->getCalledArgument('testcaseid'));
		Assert::equal('p', $this->clientMock->getCalledArgument('status'));
		Assert::equal('notes', $this->clientMock->getCalledArgument('notes'));
		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($build->id, $this->clientMock->getCalledArgument('buildid'));
		Assert::equal($platform->id, $this->clientMock->getCalledArgument('platformid'));

		Assert::true($response);
	}
}

$test = new ExecutionsTest();
$test->run();