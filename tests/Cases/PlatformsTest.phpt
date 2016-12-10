<?php
namespace Skalda\TestLinkAPI\Tests\Cases;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class PlatformsTest extends BaseTestCase
{
	public function testGetPlatformsByPlan()
	{
		$this->mockResponse('tl.getTestPlanPlatforms.xml');

		$testPlan = $this->getTestPlan();

		$response = $this->testLinkAPI->getPlatformsByPlan($testPlan);

		Assert::equal('tl.getTestPlanPlatforms', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));

		Assert::count(5, $response);

		foreach($response as $res) {
			Assert::type('Skalda\TestLinkAPI\Entities\Platform', $res);
		}

		Assert::equal('1', $response[0]->id);
		Assert::equal('4', $response[1]->id);
		Assert::equal('8', $response[2]->id);
		Assert::equal('12', $response[3]->id);
		Assert::equal('11', $response[4]->id);
	}

	public function testCreatePlatform()
	{
		$this->mockResponse('tl.createPlatform.xml');

		$testProject = $this->getTestProject();

		$response = $this->testLinkAPI->createPlatform($testProject, 'Name', 'Notes');

		Assert::equal('tl.createPlatform', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->name, $this->clientMock->getCalledArgument('testprojectname'));
		Assert::equal('Name', $this->clientMock->getCalledArgument('platformname'));
		Assert::equal('Notes', $this->clientMock->getCalledArgument('notes'));

		Assert::equal(18, $response);
	}

	public function testGetPlatformsByProject()
	{
		$this->mockResponse('tl.getProjectPlatforms.xml');

		$testProject = $this->getTestProject();

		$response = $this->testLinkAPI->getPlatformsByProject($testProject);

		Assert::equal('tl.getProjectPlatforms', $this->clientMock->getCalledMethod());

		Assert::equal($testProject->id, $this->clientMock->getCalledArgument('testprojectid'));

		Assert::count(5, $response);

		foreach($response as $res) {
			Assert::type('Skalda\TestLinkAPI\Entities\Platform', $res);
		}

		Assert::equal('1', $response[0]->id);
		Assert::equal('4', $response[1]->id);
		Assert::equal('8', $response[2]->id);
		Assert::equal('12', $response[3]->id);
		Assert::equal('11', $response[4]->id);
	}

	public function testAssignPlatformToPlan()
	{
		$this->mockResponse('tl.addPlatformToTestPlan.xml');

		$testPlan = $this->getTestPlan();
		$platform = $this->getPlatform();

		$response = $this->testLinkAPI->assignPlatformToPlan($testPlan, $platform);

		Assert::equal('tl.addPlatformToTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($platform->name, $this->clientMock->getCalledArgument('platformname'));

		Assert::true($response);
	}

	public function testRemovePlatformFromPlan()
	{
		$this->mockResponse('tl.removePlatformFromTestPlan.xml');

		$testPlan = $this->getTestPlan();
		$platform = $this->getPlatform();

		$response = $this->testLinkAPI->removePlatformFromPlan($testPlan, $platform);

		Assert::equal('tl.removePlatformFromTestPlan', $this->clientMock->getCalledMethod());

		Assert::equal($testPlan->id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($platform->name, $this->clientMock->getCalledArgument('platformname'));

		Assert::true($response);
	}
}

$test = new PlatformsTest();
$test->run();