<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Financial migration
 *
 * Color: Orange
 */
class CreateFinancial extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::create('deliver_time', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('delivertime_name', 10)->unique();
		});

		Schema::create('valid', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('valid_name', 10)->unique();
		});

		Schema::create('offer', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('description')->nullable();
			$table->text('closure')->nullable();
			$table->text('extracondition')->nullable();
			$table->boolean('downpayment')->default('N');
			$table->decimal('downpayment_amount', 9, 3)->unsigned()->nullable();
			$table->boolean('auto_email_reminder')->default('Y');
			$table->nullableTimestamps();
			$table->decimal('offer_total', 9, 3)->unsigned();
			$table->date('offer_finish')->nullable();
			$table->date('offer_make')->default(DB::raw('now()::timestamp(0)'));
			$table->string('offer_code', 50);
			$table->integer('deliver_id')->unsigned();
			$table->foreign('deliver_id')->references('id')->on('deliver_time')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('invoice_quantity')->unsigned()->default(1);
			$table->integer('valid_id')->unsigned();
			$table->foreign('valid_id')->references('id')->on('valid')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('to_contact_id')->unsigned();
			$table->foreign('to_contact_id')->references('id')->on('contact')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('from_contact_id')->unsigned();
			$table->foreign('from_contact_id')->references('id')->on('contact')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('project_id')->unsigned();
			$table->foreign('project_id')->references('id')->on('project')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('resource_id')->unsigned()->nullable();
			$table->foreign('resource_id')->references('id')->on('resource')->onUpdate('cascade')->onDelete('cascade');
			$table->boolean('include_tax')->default('Y');
			$table->boolean('only_totals')->default('Y');
			$table->boolean('seperate_subcon')->default('N');
			$table->boolean('display_worktotals')->default('N');
			$table->boolean('display_specification')->default('N');
			$table->boolean('display_description')->default('N');
		});

		Schema::create('offer_post', function(Blueprint $table)
		{
			$table->increments('id');
			$table->nullableTimestamps();
			$table->date('sent_date')->nullable();
			$table->integer('offer_id')->unsigned();
			$table->foreign('offer_id')->references('id')->on('offer')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('invoice', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('invoice_close')->default('N');
			$table->boolean('isclose')->default('N');
			$table->smallInteger('priority');
			$table->text('description')->nullable();
			$table->string('reference', 30)->index()->nullable();
			$table->string('invoice_code', 50);
			$table->string('book_code', 30)->index()->nullable();
			$table->decimal('amount', 9, 3)->nullable();
			$table->integer('payment_condition')->unsigned();
			$table->nullableTimestamps();
			$table->integer('to_contact_id')->unsigned();
			$table->foreign('to_contact_id')->references('id')->on('contact')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('from_contact_id')->unsigned();
			$table->foreign('from_contact_id')->references('id')->on('contact')->onUpdate('cascade')->onDelete('restrict');
			$table->date('bill_date')->nullable();
			$table->date('payment_date')->nullable();
			$table->date('invoice_make')->default(DB::raw('now()::timestamp(0)'));
			$table->text('closure')->nullable();
			$table->decimal('rest_21', 9, 3)->nullable();
			$table->decimal('rest_6', 9, 3)->nullable();
			$table->decimal('rest_0', 9, 3)->nullable();
			$table->boolean('auto_email_reminder')->default('Y');
			$table->integer('offer_id')->unsigned();
			$table->foreign('offer_id')->references('id')->on('offer')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('resource_id')->unsigned()->nullable();
			$table->foreign('resource_id')->references('id')->on('resource')->onUpdate('cascade')->onDelete('cascade');
			$table->boolean('include_tax')->default('Y');
			$table->boolean('only_totals')->default('Y');
			$table->boolean('seperate_subcon')->default('N');
			$table->boolean('display_worktotals')->default('N');
			$table->boolean('display_specification')->default('N');
			$table->boolean('display_description')->default('N');
		});

		Schema::create('invoice_post', function(Blueprint $table)
		{
			$table->increments('id');
			$table->nullableTimestamps();
			$table->date('sent_date')->nullable();
			$table->integer('invoice_id')->unsigned();
			$table->foreign('invoice_id')->references('id')->on('invoice')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('invoice_version', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('description')->nullable();
			$table->string('reference', 30)->index()->nullable();
			$table->string('invoice_code', 50);
			$table->string('book_code', 30)->index()->nullable();
			$table->decimal('amount', 9, 3)->nullable();
			$table->integer('payment_condition')->unsigned();
			$table->nullableTimestamps();
			$table->integer('to_contact_id')->unsigned();
			$table->foreign('to_contact_id')->references('id')->on('contact')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('from_contact_id')->unsigned();
			$table->foreign('from_contact_id')->references('id')->on('contact')->onUpdate('cascade')->onDelete('restrict');
			$table->text('closure')->nullable();
			$table->decimal('rest_21', 9, 3)->nullable();
			$table->decimal('rest_6', 9, 3)->nullable();
			$table->decimal('rest_0', 9, 3)->nullable();
			$table->integer('invoice_id')->unsigned();
			$table->foreign('invoice_id')->references('id')->on('invoice')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('resource_id')->unsigned()->nullable();
			$table->foreign('resource_id')->references('id')->on('resource')->onUpdate('cascade')->onDelete('cascade');
			$table->boolean('include_tax')->default('Y');
			$table->boolean('only_totals')->default('Y');
			$table->boolean('seperate_subcon')->default('N');
			$table->boolean('display_worktotals')->default('N');
			$table->boolean('display_specification')->default('N');
			$table->boolean('display_description')->default('N');
		});

		Schema::create('bank_account', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('account', 25);
			$table->string('account_name');
			$table->nullableTimestamps();
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('user_account')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('cashbook', function(Blueprint $table)
		{
			$table->increments('id');
			$table->nullableTimestamps();
			$table->decimal('amount', 9, 3);
			$table->date('payment_date');
			$table->string('description')->nullable();
			$table->integer('account_id')->unsigned();
			$table->foreign('account_id')->references('id')->on('bank_account')->onUpdate('cascade')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cashbook', function(Blueprint $table)
		{
			Schema::dropIfExists('cashbook');
		});

		Schema::table('bank_account', function(Blueprint $table)
		{
			Schema::dropIfExists('bank_account');
		});

		Schema::table('invoice_version', function(Blueprint $table)
		{
			Schema::dropIfExists('invoice_version');
		});

		Schema::table('invoice_post', function(Blueprint $table)
		{
			Schema::dropIfExists('invoice_post');
		});
		
		Schema::table('invoice', function(Blueprint $table)
		{
			Schema::dropIfExists('invoice');
		});

		Schema::table('offer_post', function(Blueprint $table)
		{
			Schema::dropIfExists('offer_post');
		});

		Schema::table('offer', function(Blueprint $table)
		{
			Schema::dropIfExists('offer');
		});

		Schema::table('valid', function(Blueprint $table)
		{
			Schema::dropIfExists('valid');
		});

		Schema::table('deliver_time', function(Blueprint $table)
		{
			Schema::dropIfExists('deliver_time');
		});
	}

}

