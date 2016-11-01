<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMorphRelationToRevisions extends Migration
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
        Schema::connection($this->connection)->table($this->table, function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('revisionable_type')->nullable();

            $table->index(['row_id', 'revisionable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->table($this->table, function (Blueprint $table) {
            $table->dropIndex('revisions_row_id_revisionable_type_index');
            $table->dropColumn('user_id');
            $table->dropColumn('updated_at');
            $table->dropColumn('revisionable_type');
        });
    }
}
