<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSofaRevisionsTable extends Migration
{
    /**
     * Revisions table.
     *
     * @var string
     */
    protected $table;

    /**
     * Revisions db connection.
     *
     * @var string
     */
    protected $connection;

    public function __construct()
    {
        $this->table = Config::get('sofa_revisionable.table', 'revisions');
        $this->connection = Config::get('sofa_revisionable.connection');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('action', 255);
            $table->string('table_name', 255);
            $table->integer('row_id')->unsigned();
            $table->binary('old')->nullable();
            $table->binary('new')->nullable();
            $table->string('user', 255)->nullable();
            $table->string('ip')->nullable();
            $table->string('ip_forwarded')->nullable();
            $table->timestamp('created_at');

            $table->index('action');
            $table->index(['table_name', 'row_id']);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->drop($this->table);
    }
}
