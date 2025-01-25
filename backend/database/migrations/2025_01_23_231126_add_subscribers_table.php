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
        $query = <<<SQL

        DROP TYPE IF EXISTS subscriber_status;
        DROP TYPE IF EXISTS subscriber_source;

        CREATE TYPE subscriber_status AS ENUM ('subscribed', 'unsubscribed', 'pending');
        CREATE TYPE subscriber_source AS ENUM ('console', 'form', 'import', 'auto_subscribe');

        CREATE TABLE subscribers (
            id BIGSERIAL PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            list_id BIGINT NOT NULL references lists(id),
            email VARCHAR(255) NOT NULL UNIQUE,
            status subscriber_status DEFAULT 'pending',
            subscribed_at TIMESTAMP,
            unsubscribed_at TIMESTAMP,
            source subscriber_source DEFAULT 'form',
            source_id VARCHAR(255),
            subscribe_ip VARCHAR(255),
            unsubscribe_reason VARCHAR(255)
        );

        SQL;

        DB::unprepared($query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
