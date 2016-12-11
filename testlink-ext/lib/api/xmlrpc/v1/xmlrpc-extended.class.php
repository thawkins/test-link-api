<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Filename $RCSfile: xmlrpc.php,v $
 *
 * @version $Revision: 1.91 $
 * @modified $Date: 2010/05/14 19:58:53 $ by $Author: franciscom $
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * 
 * Testlink API makes it possible to interact with Testlink  
 * using external applications and services. This makes it possible to report test results 
 * directly from automation frameworks as well as other features.
 * 
 * 
 *
 * @internal revisions
 *	
 */

require_once('./xmlrpc.class.php');

class TestlinkXMLRPCServerExtended extends TestlinkXMLRPCServer
{
	public function getProjectById($args)
	{
		$messagePrefix="(" .__FUNCTION__ . ") - ";

		$this->_setArgs($args);
		// check the tplanid
		//TODO: NEED associated RIGHT
		$checkFunctions = array('authenticate','checkTestProjectID');
		$status_ok=$this->_runChecks($checkFunctions,$messagePrefix);

		if($status_ok)
		{
			$testProjectID = $this->args[self::$testProjectIDParamName];
			$info=$this->tprojectMgr->get_by_id($testProjectID);
			return $info;
		}
		else
		{
			return $this->errors;
		}
	}

	/**
	 * Gets info about target test project
	 *
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param string $args["testplanid"]
	 * @return mixed $resultInfo
	 * @access public
	 */
	public function getTestPlanById($args)
	{
		$msg_prefix="(" .__FUNCTION__ . ") - ";
		$status_ok=true;
		$this->_setArgs($args);
		if($this->authenticate())
		{
			$keys2check = array(self::$testPlanIDParamName);
			foreach($keys2check as $key)
			{
				$names[$key]=$this->_isParamPresent($key,$msg_prefix,self::SET_ERROR) ? trim($this->args[$key]) : '';
				if($names[$key]=='')
				{
					$status_ok=false;
					break;
				}
			}
		}

		if($status_ok)
		{
			$name=trim($names[self::$testPlanIDParamName]);
			$info = $this->tplanMgr->get_by_id($name);
			if( !($status_ok=!is_null($info)) )
			{
				$msg = $msg_prefix . sprintf(TESTPLANNAME_DOESNOT_EXIST_STR,$name);
				$this->errors[] = new IXR_Error(TESTPLANNAME_DOESNOT_EXIST, $msg);
			}
		}

		return $status_ok ? $info : $this->errors;
	}

	public function getBuildById($args)
	{
		$operation=__FUNCTION__;
		$msg_prefix="({$operation}) - ";
		$this->_setArgs($args);

		$status_ok=true;
		$checkFunctions = array('authenticate');
		$status_ok=$this->_runChecks($checkFunctions,$msg_prefix);

		if( $status_ok )
		{
			$testPlanID = $this->args[self::$testPlanIDParamName];
			$buildID = $this->args[self::$buildIDParamName];
			$build = $this->tplanMgr->get_build_by_id($testPlanID, $buildID);

			if( !($status_ok=!is_null($build)) )
			{
				$msg = $msg_prefix . sprintf(INVALID_BUILDID_STR,$buildID);
				$this->errors[] = new IXR_Error(INVALID_BUILDID, $msg);
			}
		}
		return $status_ok ? $build : $this->errors;
	}

	public function initMethodYellowPages()
	{
		parent::initMethodYellowPages();
		$this->methods = array_merge($this->methods, [
			'tl.getProjectById' => 'this:getProjectById',
			'tl.getTestPlanById' => 'this:getTestPlanById',
			'tl.getBuildById' => 'this:getBuildById',
		]);
	}
}
?>
