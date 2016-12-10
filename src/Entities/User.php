<?php
namespace Skalda\TestLinkAPI\Entities;

class User extends BaseEntity
{
	public $firstName;
	public $lastName;
	public $emailAddress;
	public $locale;
	public $isActive;
	public $defaultTestprojectID;
	public $globalRole;
	public $globalRoleID;
	public $tprojectRoles;
	public $tplanRoles;
	public $login;
	public $dbID;

}