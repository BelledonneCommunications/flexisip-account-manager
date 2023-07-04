<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('contacts_list_contact');
        Schema::dropIfExists('account_contacts_list');
        Schema::dropIfExists('contacts_lists');

        Schema::create('contacts_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('account_contacts_list', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();
            $table->bigInteger('contacts_list_id')->unsigned();
            $table->foreign('account_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
            $table->foreign('contacts_list_id')->references('id')
                  ->on('contacts_lists')->onDelete('cascade');
            $table->unique(['account_id', 'contacts_list_id']);
            $table->timestamps();
        });

        Schema::create('contacts_list_contact', function (Blueprint $table) {
            $table->integer('contact_id')->unsigned();
            $table->bigInteger('contacts_list_id')->unsigned();
            $table->foreign('contact_id')->references('id')
                  ->on('accounts')->onDelete('cascade');
            $table->foreign('contacts_list_id')->references('id')
                  ->on('contacts_lists')->onDelete('cascade');
            $table->unique(['contact_id', 'contacts_list_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_contacts_list');
        Schema::dropIfExists('contacts_list_contact');
        Schema::dropIfExists('contacts_lists');
    }
};
