<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTableAddEcommerceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('slug')->unique()->after('name');

            $table->integer('weight')
                  ->comment('weight in gram')
                  ->after('stock');

            $table->integer('length')->nullable()->after('weight');
            $table->integer('width')->nullable()->after('length');
            $table->integer('height')->nullable()->after('width');

            $table->enum('status', ['draft', 'active', 'inactive'])
                  ->default('draft')
                  ->after('thumbnail');

            $table->integer('sold_count')
                  ->default(0)
                  ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'slug',
                'weight',
                'length',
                'width',
                'height',
                'status',
                'sold_count'
            ]);
        });
    }
}
