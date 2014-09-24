<?php

class ProjectType extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'project_type';

	public $timestamps = false;

	public function projectStep() {
		return $this->belongsToMany('ProjectStep', 'project_type_project_step', 'type_id', 'step_id');
	}
}
