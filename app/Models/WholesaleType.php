<?php

namespace BynqIO\Dynq\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleType extends Model {

	protected $table = 'wholesale_type';
	protected $guarded = array('id');

	public $timestamps = false;

}
