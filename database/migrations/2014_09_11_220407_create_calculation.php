<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Calculation migration
 *
 * Color: Black
 */
class CreateCalculation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('calculation_labor', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('rate', 6, 3)->unsigned()->nullable();
			$table->decimal('amount', 9, 3)->unsigned()->index();
			$table->decimal('less_amount', 9, 3)->unsigned()->index()->nullable();
			$table->boolean('isless')->default('false');
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('calculation_material', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('material_name', 100);
			$table->string('unit', 10);
			$table->decimal('rate', 9, 3)->unsigned()->index();
			$table->decimal('amount', 9, 3)->unsigned()->index();
			$table->decimal('less_rate', 9, 3)->unsigned()->index()->nullable();
			$table->decimal('less_amount', 9, 3)->unsigned()->index()->nullable();
			$table->boolean('isless')->default('false');
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('calculation_equipment', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('equipment_name', 100);
			$table->string('unit', 10);
			$table->decimal('rate', 9, 3)->unsigned()->index();
			$table->decimal('amount', 9, 3)->unsigned()->index();
			$table->decimal('less_rate', 9, 3)->unsigned()->index()->nullable();
			$table->decimal('less_amount', 9, 3)->unsigned()->index()->nullable();
			$table->boolean('isless')->default('false');
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('more_labor', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('rate', 6, 3)->unsigned()->index();
			$table->decimal('amount', 9, 3)->unsigned()->index();
			$table->text('note')->nullable();
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('hour_id')->unsigned()->nullable();
			$table->foreign('hour_id')->references('id')->on('timesheet')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('more_material', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('material_name', 100);
			$table->string('unit', 10);
			$table->decimal('rate', 9, 3)->unsigned()->index();
			$table->decimal('amount', 9, 3)->unsigned()->index();
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('more_equipment', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('equipment_name', 100);
			$table->string('unit', 10);
			$table->decimal('rate', 9, 3)->unsigned()->index();
			$table->decimal('amount', 9, 3)->unsigned()->index();
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('estimate_labor', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('rate', 6, 3)->unsigned()->nullable()->index();
			$table->decimal('amount', 9, 3)->unsigned()->nullable()->index();
			$table->decimal('set_rate', 6, 3)->unsigned()->nullable()->index();
			$table->decimal('set_amount', 9, 3)->unsigned()->nullable()->index();
			$table->boolean('original');
			$table->boolean('isset');
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('hour_id')->unsigned()->nullable();
			$table->foreign('hour_id')->references('id')->on('timesheet')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('estimate_material', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('material_name', 100)->nullable();
			$table->string('unit', 10)->nullable();
			$table->decimal('rate', 9, 3)->unsigned()->nullable()->index();
			$table->decimal('amount', 9, 3)->unsigned()->nullable()->index();
			$table->string('set_material_name', 50)->nullable();
			$table->string('set_unit', 10)->nullable();
			$table->decimal('set_rate', 9, 3)->unsigned()->nullable()->index();
			$table->decimal('set_amount', 9, 3)->unsigned()->nullable()->index();
			$table->boolean('original');
			$table->boolean('isset');
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('estimate_equipment', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('equipment_name', 100)->nullable();
			$table->string('unit', 10)->nullable();
			$table->decimal('rate', 9, 3)->unsigned()->nullable()->index();
			$table->decimal('amount', 9, 3)->unsigned()->nullable()->index();
			$table->string('set_equipment_name', 50)->nullable();
			$table->string('set_unit', 10)->nullable();
			$table->decimal('set_rate', 9, 3)->unsigned()->nullable()->index();
			$table->decimal('set_amount', 9, 3)->unsigned()->nullable()->index();
			$table->boolean('original');
			$table->boolean('isset');
			$table->integer('activity_id')->unsigned();
			$table->foreign('activity_id')->references('id')->on('activity')->onUpdate('cascade')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('estimate_equipment', function(Blueprint $table)
		{
			Schema::dropIfExists('estimate_equipment');
		});

		Schema::table('estimate_material', function(Blueprint $table)
		{
			Schema::dropIfExists('estimate_material');
		});

		Schema::table('estimate_labor', function(Blueprint $table)
		{
			Schema::dropIfExists('estimate_labor');
		});

		Schema::table('more_equipment', function(Blueprint $table)
		{
			Schema::dropIfExists('more_equipment');
		});

		Schema::table('more_material', function(Blueprint $table)
		{
			Schema::dropIfExists('more_material');
		});

		Schema::table('more_labor', function(Blueprint $table)
		{
			Schema::dropIfExists('more_labor');
		});
		Schema::table('calculation_equipment', function(Blueprint $table)
		{
			Schema::dropIfExists('calculation_equipment');
		});

		Schema::table('calculation_material', function(Blueprint $table)
		{
			Schema::dropIfExists('calculation_material');
		});

		Schema::table('calculation_labor', function(Blueprint $table)
		{
			Schema::dropIfExists('calculation_labor');
		});
	}

}
