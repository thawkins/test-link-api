<?php
namespace Skalda\TestLinkAPI\Tests\Cases;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class TestPlansTest extends BaseTestCase
{
	public function testGetPlansByProject()
	{
		$this->mockResponse('tl.getProjectTestPlans.xml');

		$testProject = $this->getTestProject();

		$response = $this->testLinkAPI->getPlansByProject($testProject);

		Assert::equal('tl.getProjectTestPlans', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->id, $this->clientMock->getCalledArgument('testprojectid'));

		Assert::count(2, $response);

		foreach($response as $res) {
			Assert::type('Skalda\TestLinkAPI\Entities\TestPlan', $res);
		}

		Assert::equal('383', $response[0]->id);
		Assert::equal('384', $response[1]->id);
	}

	public function testGetPlanById()
	{
		$this->mockResponse('tl.getTestPlanById.xml');

		$response = $this->testLinkAPI->getPlanById(383);

		Assert::equal('tl.getTestPlanById', $this->clientMock->getCalledMethod());

		Assert::equal(383, $this->clientMock->getCalledArgument('testplanid'));

		Assert::type('Skalda\TestLinkAPI\Entities\TestPlan', $response);

		Assert::equal('383', $response->id);
	}

	public function testCreateTestPlan()
	{
		$this->mockResponse('tl.createTestPlan.xml');

		$testProject = $this->getTestProject();

		$response = $this->testLinkAPI->createTestPlan($testProject, 'Name', 'Notes', true, false);

		Assert::equal('tl.createTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->prefix, $this->clientMock->getCalledArgument('prefix'));
		Assert::equal('Name', $this->clientMock->getCalledArgument('testplanname'));
		Assert::equal('Notes', $this->clientMock->getCalledArgument('notes'));
		Assert::true($this->clientMock->getCalledArgument('active'));
		Assert::false($this->clientMock->getCalledArgument('public'));

		Assert::equal(430, $response);
	}

	public function testGetTotalsByPlan()
	{
		$this->mockResponse('tl.getTotalsForTestPlan.xml');

		$testPlan = $this->getTestPlan();

		$response = $this->testLinkAPI->getTotalsByPlan($testPlan);

		Assert::equal('tl.getTotalsForTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));

		Assert::type('array', $response);
	}

	public function testDeleteTestPlan()
	{
		$this->mockResponse('tl.deleteTestPlan.xml');

		$testPlan = $this->getTestPlan();

		$response = $this->testLinkAPI->deleteTestPlan($testPlan);

		Assert::equal('tl.deleteTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));

		Assert::true($response);
	}
}

$test = new TestPlansTest();
$test->run();