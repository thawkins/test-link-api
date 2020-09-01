<?php
namespace thawkins\TestLinkAPI\Tests\Cases;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class UsersTest extends BaseTestCase
{

	public function testDoesUserExist()
	{
		$this->mockResponse('tl.doesUserExist.xml');

		$user = $this->getUser();

		$response = $this->testLinkAPI->doesUserExist($user->login);

		Assert::equal('tl.doesUserExist', $this->clientMock->getCalledMethod());

		Assert::equal($user->login, $this->clientMock->getCalledArgument('user'));

		Assert::true($response);
	}

	public function testGetUserByLogin()
	{
		$this->mockResponse('tl.getUserByLogin.xml');

		$user = $this->getUser();

		$response = $this->testLinkAPI->getUserByLogin($user->login);

		Assert::equal('tl.getUserByLogin', $this->clientMock->getCalledMethod());

		Assert::equal($user->login, $this->clientMock->getCalledArgument('user'));

		Assert::type('thawkins\TestLinkAPI\Entities\User', $response);

		Assert::equal('1', $response->dbID);
	}

	public function testGetUserById()
	{
		$this->mockResponse('tl.getUserByID.xml');

		$user = $this->getUser();

		$response = $this->testLinkAPI->getUserById($user->dbID);

		Assert::equal('tl.getUserByID', $this->clientMock->getCalledMethod());

		Assert::equal($user->dbID, $this->clientMock->getCalledArgument('userid'));

		Assert::type('thawkins\TestLinkAPI\Entities\User', $response);

		Assert::equal('1', $response->dbID);
	}

	public function testGetUserByAssignedTestCaseInstance()
	{
		$this->mockResponse('tl.getTestCaseAssignedTester.xml');

		$testCaseInstance = $this->getPlanTestCaseInstance();

		$response = $this->testLinkAPI->getUserByAssignedTestCaseInstance($testCaseInstance);

		Assert::equal('tl.getTestCaseAssignedTester', $this->clientMock->getCalledMethod());

		Assert::equal($testCaseInstance->getBuild()->testplan_id, $this->clientMock->getCalledArgument('testplanid'));
		Assert::equal($testCaseInstance->getTestCase()->getTestCase()->full_tc_external_id, $this->clientMock->getCalledArgument('testcaseexternalid'));
		Assert::equal($testCaseInstance->getBuild()->id, $this->clientMock->getCalledArgument('buildid'));

		Assert::type('thawkins\TestLinkAPI\Entities\User', $response);

		Assert::equal('admin', $response->login);
	}
}

$test = new UsersTest();
$test->run();