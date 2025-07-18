<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFormaPagamento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formapagamento', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nomeFormaPagamento', 191)->nullable(false);
            $table->tinyInteger('ativoFormaPagamento')->nullable(false);
            $table->tinyInteger('excluidoFormaPagamento')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formapagamento');
    }
}
