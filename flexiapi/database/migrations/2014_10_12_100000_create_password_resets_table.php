<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    public function up()
    {
        if (config('database.default') == 'mysql'){
          //Need to execute the workaround only if the variable sql_require_primary_key exists (>= mysql 8) and if it is set to true
          $mysql_version = DB::statement("SHOW VARIABLES LIKE 'version'");
          if (0 !== preg_match('/(\d+\.?)+$/', $mysql_version, $matches)) {
            echo 'original: ' . $version . '; extracted: ' . $matches[0] . PHP_EOL;
            if (0 !== preg_match('/(^\d)/', $matches[0], $matches_start){
              if ($matches_start >= 8){
                DB::statement('SET SESSION sql_require_primary_key=0');
              }
              else{
                //mysql isn't the version 8
              }
            }
            else{
              //error or no match found for first number in version string
            }
          }
          else{
            //error or no match found for version number in string
          }
        }
        else {
          //database default isn't mysql
        }
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
