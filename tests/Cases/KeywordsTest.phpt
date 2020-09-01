<?php
namespace thawkins\TestLinkAPI\Tests\Cases;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class KeywordsTest extends BaseTestCase
{

	public function testGetKeywordsByProject()
	{
		$this->mockResponse('tl.getProjectKeywords.xml');

		$project = $this->getTestProject();

		$response = $this->testLinkAPI->getKeywordsByProject($project);

		Assert::equal('tl.getProjectKeywords', $this->clientMock->getCalledMethod());

		Assert::equal($project->id, $this->clientMock->getCalledArgument('testprojectid'));

		Assert::count(2, $response);

		foreach($response as $res) {
			Assert::type('thawkins\TestLinkAPI\Entities\Keyword', $res);
		}

		Assert::equal('Keyword one', $response[0]->name);
		Assert::equal('Keyword two', $response[1]->name);
	}

	public function testGetKeywordsByTestCase()
	{
		$this->mockResponse('tl.getTestCaseKeywords.xml');

		$testCase = $this->getTestCase();
		$testCase->testcase_id = 268;

		$response = $this->testLinkAPI->getKeywordsByTestCase($testCase);

		Assert::equal('tl.getTestCaseKeywords', $this->clientMock->getCalledMethod());

		Assert::equal($testCase->testcase_id, $this->clientMock->getCalledArgument('testcaseid'));

		Assert::count(2, $response);

		foreach($response as $res) {
			Assert::type('thawkins\TestLinkAPI\Entities\Keyword', $res);
		}

		Assert::equal('Keyword one', $response[0]->name);
		Assert::equal('Keyword two', $response[1]->name);
	}

	public function testAssignKeywordsToTestCase()
	{
		$this->mockResponse('tl.addTestCaseKeywords.xml');

		$testCase = $this->getTestCase();

		$keyword = $this->getKeyword();

		$response = $this->testLinkAPI->assignKeywordsToTestCase($testCase, [$keyword]);

		Assert::equal('tl.addTestCaseKeywords', $this->clientMock->getCalledMethod());

		Assert::equal($keyword->name, $this->clientMock->getCalledArgument('keywords')[$testCase->full_tc_external_id][0]);

		Assert::true($response);
	}

	public function testRemoveKeywordsFromTestCase()
	{
		$this->mockResponse('tl.removeTestCaseKeywords.xml');

		$testCase = $this->getTestCase();

		$keyword = $this->getKeyword();

		$response = $this->testLinkAPI->removeKeywordsFromTestCase($testCase, [$keyword]);

		Assert::equal('tl.removeTestCaseKeywords', $this->clientMock->getCalledMethod());

		Assert::equal($keyword->name, $this->clientMock->getCalledArgument('keywords')[$testCase->full_tc_external_id][0]);

		Assert::true($response);
	}
}

$test = new KeywordsTest();
$test->run();