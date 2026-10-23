
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                  ->constrained()
                  ->restrictOnDelete();

            $table->string('source_name', 150)->nullable();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->text('url');
            $table->char('url_hash', 64)->unique();
            $table->text('image_url')->nullable();

            $table->timestamp('published_at')
                  ->nullable()
                  ->index();

            $table->timestamp('fetched_at')->useCurrent();

            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_articles');
    }
};
