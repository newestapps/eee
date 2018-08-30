<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNw3eTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        if (!Schema::hasTable('nw3e_indexes')) {
            Schema::create('nw3e_indexes', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('uuid')->unique()->required();
                $table->softDeletes();
                $table->timestamps();
                $table->index('uuid');
            });
        }

        if (!Schema::hasTable('nw3e_ssl_credentials')) {
            Schema::create('nw3e_ssl_credentials', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('nw3e_index')->required()->unsigned();
                $table->text('_pubkey')->required();
                $table->text('_privkey')->required();
                $table->integer('keybits')->default('4096');
                $table->string('keytype')->default(OPENSSL_KEYTYPE_RSA);
                $table->softDeletes();
                $table->timestamps();

                $table->index(['created_at']);

                $table->foreign('nw3e_index')->references('id')->on('nw3e_indexes');
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('users');
    }
}
