<?php
namespace thawkins\TestLinkAPI\Tests\Cases;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class TestSuitesTest extends BaseTestCase
{

	public function testGetTestSuitesByPlan()
	{
		$this->mockResponse('tl.getTestSuitesForTestPlan.xml');

		$testPlan = $this->getTestPlan();

		$response = $this->testLinkAPI->getTestSuitesByPlan($testPlan);

		Assert::equal('tl.getTestSuitesForTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));

		Assert::count(11, $response);

		foreach($response as $res) {
			Assert::type('thawkins\TestLinkAPI\Entities\TestSuite', $res);
		}

		Assert::equal('2', $response[0]->id);
		Assert::equal('240', $response[1]->id);
		Assert::equal('15', $response[2]->id);
		Assert::equal('233', $response[3]->id);
		Assert::equal('3', $response[4]->id);
		Assert::equal('241', $response[5]->id);
		Assert::equal('206', $response[6]->id);
		Assert::equal('178', $response[7]->id);
		Assert::equal('179', $response[8]->id);
		Assert::equal('232', $response[9]->id);
		Assert::equal('231', $response[10]->id);

	}

	public function testGetFirstLevelTestSuitesByProject()
	{
		$this->mockResponse('tl.getFirstLevelTestSuitesForTestProject.xml');

		$testProject = $this->getTestProject();

		$response = $this->testLinkAPI->getFirstLevelTestSuitesByProject($testProject);

		Assert::equal('tl.getFirstLevelTestSuitesForTestProject', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->id, $this->clientMock->getCalledArgument('testprojectid'));

		Assert::count(1, $response);

		foreach($response as $res) {
			Assert::type('thawkins\TestLinkAPI\Entities\TestSuite', $res);
		}

		Assert::equal('265', $response[0]->id);
	}

	public function testUpdateTestSuite()
	{
		$this->mockResponse('tl.updateTestSuite.xml');

		$testProject = $this->getTestProject();
		$testSuite = $this->getTestSuite();

		$response = $this->testLinkAPI->updateTestSuite($testProject, $testSuite, 'Name', 'Detail');

		Assert::equal('tl.updateTestSuite', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->id, $this->clientMock->getCalledArgument('testprojectid'));
		Assert::equal($testSuite->id, $this->clientMock->getCalledArgument('testsuiteid'));
		Assert::equal('Name', $this->clientMock->getCalledArgument('testsuitename'));
		Assert::equal('Detail', $this->clientMock->getCalledArgument('details'));

		Assert::true($response);
	}

	public function testCreateTestSuite()
	{
		$this->mockResponse('tl.createTestSuite.xml');

		$testProject = $this->getTestProject();

		$response = $this->testLinkAPI->createTestSuite($testProject, 'Name', 'Detail');

		Assert::equal('tl.createTestSuite', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->id, $this->clientMock->getCalledArgument('testprojectid'));
		Assert::equal('Name', $this->clientMock->getCalledArgument('testsuitename'));
		Assert::equal('Detail', $this->clientMock->getCalledArgument('details'));

		Assert::equal(429, $response);
	}

	public function testGetTestSuiteById()
	{
		$this->mockResponse('tl.getTestSuiteByID.xml');

		$response = $this->testLinkAPI->getTestSuiteById(356);

		Assert::equal('tl.getTestSuiteByID', $this->clientMock->getCalledMethod());

		Assert::equal(356, $this->clientMock->getCalledArgument('testsuiteid'));

		Assert::type('thawkins\TestLinkAPI\Entities\TestSuite', $response);

		Assert::equal('356', $response->id);
	}

	public function testGetTestSuitesByTestSuite()
	{
		$this->mockResponse('tl.getTestSuitesForTestSuite.xml');

		$testSuite = $this->getTestSuite();

		$response = $this->testLinkAPI->getTestSuitesByTestSuite($testSuite);

		Assert::equal('tl.getTestSuitesForTestSuite', $this->clientMock->getCalledMethod());

		Assert::equal($testSuite->id, $this->clientMock->getCalledArgument('testsuiteid'));

		Assert::count(1, $response);

		foreach($response as $res) {
			Assert::type('thawkins\TestLinkAPI\Entities\TestSuite', $res);
		}

		Assert::equal('266', $response[0]->id);
	}

	public function testUploadTestSuiteAttachment()
	{
		$this->mockResponse('tl.uploadAttachment.xml');

		$testSuite = $this->getTestSuite();

		$response = $this->testLinkAPI->uploadTestSuiteAttachment($testSuite, 'filename.txt', 'test/plain', 'eW8h');

		Assert::equal('tl.uploadTestSuiteAttachment', $this->clientMock->getCalledMethod());

		Assert::equal($testSuite->id, $this->clientMock->getCalledArgument('testsuiteid'));
		Assert::equal('filename.txt', $this->clientMock->getCalledArgument('filename'));
		Assert::equal('test/plain', $this->clientMock->getCalledArgument('filetype'));
		Assert::equal('eW8h', $this->clientMock->getCalledArgument('content'));

		Assert::type('array', $response);

		Assert::equal('filename.txt', $response['file_name']);
	}
}

$test = new TestSuitesTest();
$test->run();