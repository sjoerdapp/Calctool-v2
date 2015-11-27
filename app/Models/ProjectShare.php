<?php

namespace Calctool\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectShare extends Model {

	protected $table = 'project';
	protected $guarded = array('id', 'token');

	public function __construct()
	{
		$this->token = \Calctool::generateToken();
	}

}
