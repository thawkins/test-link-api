<?php
namespace Skalda\TestLinkAPI\Tests\Cases;

use Skalda\TestLinkAPI\Entities\TestProject;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class TestProjectTest extends BaseTestCase
{
	public function testGetProjects()
	{
		$this->mockResponse('tl.getProjects.xml');

		$response = $this->testLinkAPI->getProjects();

		Assert::equal('tl.getProjects', $this->clientMock->getCalledMethod());

		Assert::count(3, $response);

		foreach($response as $resp) {
			Assert::type('Skalda\TestLinkAPI\Entities\TestProject', $resp);
		}

		Assert::equal('354', $response[0]->id);
		Assert::equal('1', $response[1]->id);
		Assert::equal('264', $response[2]->id);
	}

	public function testGetProjectById()
	{
		$this->mockResponse('tl.getProjectById.xml');

		$response = $this->testLinkAPI->getProjectById(1);

		Assert::equal('tl.getProjectById', $this->clientMock->getCalledMethod());

		Assert::equal(1, $this->clientMock->getCalledArgument('testprojectid'));

		Assert::type('Skalda\TestLinkAPI\Entities\TestProject', $response);

		Assert::equal('1', $response->id);
	}

	public function testCreateTestProject()
	{
		$this->mockResponse('tl.createTestProject.xml');

		$response = $this->testLinkAPI->createTestProject('name', 'PRE', 'notes', true, true, false, false, false);

		Assert::equal('tl.createTestProject', $this->clientMock->getCalledMethod());

		Assert::equal('name', $this->clientMock->getCalledArgument('testprojectname'));
		Assert::equal('PRE', $this->clientMock->getCalledArgument('testcaseprefix'));
		Assert::equal('notes', $this->clientMock->getCalledArgument('notes'));
		Assert::truthy($this->clientMock->getCalledArgument('active'));
		Assert::truthy($this->clientMock->getCalledArgument('public'));
		Assert::falsey($this->clientMock->getCalledArgument('options')['requirementsEnabled']);
		Assert::falsey($this->clientMock->getCalledArgument('options')['testPriorityEnabled']);
		Assert::falsey($this->clientMock->getCalledArgument('options')['automationEnabled']);
		Assert::truthy($this->clientMock->getCalledArgument('options')['inventoryEnabled']);

		Assert::equal(391, $response);
	}

	public function testUploadTestProjectAttachment()
	{
		$this->mockResponse('tl.uploadAttachment.xml');

		$testProject = new TestProject($this->testLinkAPI);
		$testProject->id = 1;

		$response = $this->testLinkAPI->uploadTestProjectAttachment($testProject, 'filename.txt', 'test/plain', 'eW8h');

		Assert::equal('tl.uploadTestProjectAttachment', $this->clientMock->getCalledMethod());

		Assert::equal(1, $this->clientMock->getCalledArgument('testprojectid'));
		Assert::equal('filename.txt', $this->clientMock->getCalledArgument('filename'));
		Assert::equal('test/plain', $this->clientMock->getCalledArgument('filetype'));
		Assert::equal('eW8h', $this->clientMock->getCalledArgument('content'));

		Assert::type('array', $response);

		Assert::equal('filename.txt', $response['file_name']);
	}

	public function testDeleteTestProject()
	{
		$this->mockResponse('tl.deleteTestProject.xml');

		$testProject = new TestProject($this->testLinkAPI);
		$testProject->prefix = 'PRE';

		$response = $this->testLinkAPI->deleteTestProject($testProject);

		Assert::equal('tl.deleteTestProject', $this->clientMock->getCalledMethod());

		Assert::equal('PRE', $this->clientMock->getCalledArgument('prefix'));

		Assert::true($response);
	}
}

$test = new TestProjectTest();
$test->run();