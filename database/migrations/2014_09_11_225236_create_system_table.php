<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * System migration
 *
 * Color: White
 */
class CreateSystemTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_message', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('level');
			$table->text('content');
			$table->boolean('active')->default('N');
			$table->nullableTimestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('system_message', function(Blueprint $table)
		{
			Schema::dropIfExists('system_message');
		});
	}

}
