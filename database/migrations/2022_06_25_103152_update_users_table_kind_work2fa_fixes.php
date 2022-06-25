<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableKindWork2faFixes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_fa_rember_token', 255)->nullable();
            $table->timestamp('two_fa_rember_token_expires_at', 0)->nullable();
        });
        if (Schema::hasColumn('users', '2fa')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('2fa', 'TwoFA');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_fa_rember_token', 'two_fa_rember_token_expires_at']);
        });
    }
}
