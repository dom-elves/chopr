<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // drop old column name & re-add with key constraints
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn('collector_group_user_id');
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->foreignId('user_id')->after('group_id')->constrained()->cascadeOnDelete();
        }); 

        // same again for groups
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('owner_id');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
        });

        // shares already has key contrains so just needs to be renamed
        Schema::table('shares', function (Blueprint $table) {
            $table->renameColumn('group_user_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->renameColumn('user_id', 'owner_id' );
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->renameColumn('user_id', 'collector_group_user_id');
        });

        Schema::table('shares', function (Blueprint $table) {
            $table->renameColumn('user_id', 'group_user_id');
        });
    }
};
