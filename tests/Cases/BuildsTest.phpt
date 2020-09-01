<?php
namespace thawkins\TestLinkAPI\Tests\Cases;

use thawkins\TestLinkAPI\Client;
use thawkins\TestLinkAPI\Entities\TestPlan;
use thawkins\TestLinkAPI\Tests\Mockup\ClientMock;
use Tester\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';

class BuildsTest extends BaseTestCase
{
	public function testGetBuildsByPlan()
	{
		$this->mockResponse('tl.getBuildsForTestPlan.xml');

		$testPlan = new TestPlan($this->testLinkAPI);
		$testPlan->id = 383;

		$response = $this->testLinkAPI->getBuildsByPlan($testPlan);

		Assert::equal('tl.getBuildsForTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal(383, $this->clientMock->getCalledArgument('testplanid'));

		Assert::count(2, $response);

		foreach($response as $resp) {
			Assert::type('thawkins\TestLinkAPI\Entities\Build', $resp);
		}

		Assert::equal('1', $response[0]->id);
		Assert::equal('2', $response[1]->id);
	}

	public function testGetBuildById()
	{
		$this->mockResponse('tl.getBuildById.xml');

		$testPlan = new TestPlan($this->testLinkAPI);
		$testPlan->id = 385;

		$response = $this->testLinkAPI->getBuildById(3, $testPlan);

		Assert::equal('tl.getBuildById', $this->clientMock->getCalledMethod());

		Assert::equal(3, $this->clientMock->getCalledArgument('buildid'));
		Assert::equal(385, $this->clientMock->getCalledArgument('testplanid'));

		Assert::type('thawkins\TestLinkAPI\Entities\Build', $response);

		Assert::equal('3', $response->id);
	}

	public function testGetExecPerBuildByPlan()
	{
		$this->mockResponse('tl.getExecCountersByBuild.xml');

		$testPlan = new TestPlan($this->testLinkAPI);
		$testPlan->id = 385;

		$response = $this->testLinkAPI->getExecPerBuildByPlan($testPlan);

		Assert::equal('tl.getExecCountersByBuild', $this->clientMock->getCalledMethod());

		Assert::equal(385, $this->clientMock->getCalledArgument('testplanid'));

		Assert::type('array', $response);
	}

	public function testGetLatestBuildByPlan()
	{
		$this->mockResponse('tl.getLatestBuildForTestPlan.xml');

		$testPlan = new TestPlan($this->testLinkAPI);
		$testPlan->id = 383;

		$response = $this->testLinkAPI->getLatestBuildByPlan($testPlan);

		Assert::equal('tl.getLatestBuildForTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal(383, $this->clientMock->getCalledArgument('testplanid'));

		Assert::type('thawkins\TestLinkAPI\Entities\Build', $response);

		Assert::equal('2', $response->id);
	}

	public function testCreateBuild()
	{
		$this->mockResponse('tl.createBuild.xml');

		$testPlan = new TestPlan($this->testLinkAPI);
		$testPlan->id = 385;

		$response = $this->testLinkAPI->createBuild($testPlan, 'name', 'notes', true, false, '2017-01-01');

		Assert::equal('tl.createBuild', $this->clientMock->getCalledMethod());

		Assert::equal(385, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal('name', $this->clientMock->getCalledArgument('buildname'));
		Assert::equal('notes', $this->clientMock->getCalledArgument('buildnotes'));
		Assert::true($this->clientMock->getCalledArgument('active'));
		Assert::false($this->clientMock->getCalledArgument('open'));
		Assert::equal('2017-01-01', $this->clientMock->getCalledArgument('releasedate'));

		Assert::equal(8, $response);
	}
}

$test = new BuildsTest();
$test->run();