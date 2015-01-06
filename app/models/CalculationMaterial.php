<?php

class CalculationMaterial extends Eloquent {

	protected $table = 'calculation_material';
	protected $guarded = array('id');

	public $timestamps = false;

	public function activity() {
		return $this->hasOne('Activity');
	}

	public function tax() {
		return $this->hasOne('Tax','id', 'tax_id');
	}

}
