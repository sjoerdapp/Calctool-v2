<?php

class Payment extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'payment';

	public $timestamps = false;

	public function user() {
		return $this->hasOne('User');
	}
}
