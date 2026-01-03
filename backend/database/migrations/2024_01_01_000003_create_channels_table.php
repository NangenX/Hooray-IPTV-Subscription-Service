<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('stream_url');
            $table->string('logo_url')->nullable();
            $table->string('category')->nullable();
            $table->string('language')->nullable();
            $table->string('country', 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('quality', 10)->nullable();
            $table->string('tvg_id')->nullable();
            $table->string('tvg_name')->nullable();
            $table->string('tvg_logo')->nullable();
            $table->string('group_title')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // 复合唯一索引：name + stream_url 完全相同才认为是重复
            $table->unique(['name', 'stream_url'], 'unique_name_url');
            $table->index('tvg_id');
            $table->index('group_title');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('channels');
    }
};
