<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('target')->nullable()->default('_self');
            $table->string('icon_class');
            $table->string('color')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('order');
            $table->string('route')->nullable();
            $table->string('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('menu_id');
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
        Schema::dropIfExists('menu_items');
    }
}
