<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCosmosenderTables extends Migration
{
    /**
     * Safe Non-Destructive Migration:
     * Hanya membuat tabel apabila tabel belum ada di database,
     * sehingga data lama pada supresso_mailblast dijamin 100% aman.
     */
    public function up()
    {
        if (!Schema::hasTable('m_server')) {
            Schema::create('m_server', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->nullable();
                $table->string('domain', 100)->nullable();
                $table->string('color', 50)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('m_service')) {
            Schema::create('m_service', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('server_id')->nullable();
                $table->string('service', 100)->nullable();
                $table->string('color', 50)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
            });
        }

        if (!Schema::hasTable('m_brand')) {
            Schema::create('m_brand', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->nullable();
                $table->text('email_alias')->nullable();
                $table->string('slug', 100)->nullable();
                $table->string('domain', 100)->nullable();
                $table->string('actual_path', 100)->nullable();
                $table->string('content_domain', 255)->nullable();
                $table->string('aseet_namespace', 100)->nullable();
                $table->unsignedInteger('service_id')->nullable();
                $table->timestamp('email_last_sync')->useCurrent();
                $table->string('flag_blast', 1)->nullable();
                $table->string('flag_email_sync', 1)->nullable();
                $table->integer('blast_limit')->default(0)->nullable();
                $table->integer('blast_hour_interval')->default(0)->nullable();
                $table->integer('last_time_sequence')->nullable();
                $table->unsignedInteger('last_email_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->useCurrent();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->timestamp('last_blast_time')->useCurrent();
            });
        }

        if (!Schema::hasTable('m_email_host')) {
            Schema::create('m_email_host', function (Blueprint $table) {
                $table->increments('id');
                $table->string('host', 100)->nullable();
                $table->unsignedInteger('server_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('m_email_relay')) {
            Schema::create('m_email_relay', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('host_id')->nullable();
                $table->string('email', 100)->nullable();
                $table->text('email_alias')->nullable();
                $table->text('email_from')->nullable();
                $table->text('password')->nullable();
                $table->integer('port')->default(465)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('m_blast_rule')) {
            Schema::create('m_blast_rule', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->unsignedInteger('brand_id')->nullable();
                $table->unsignedInteger('host_id')->nullable();
                $table->string('type', 50)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 255)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 255)->nullable();
            });
        }

        if (!Schema::hasTable('m_blast_rule_detail')) {
            Schema::create('m_blast_rule_detail', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('relay_id')->nullable();
                $table->unsignedInteger('rule_id')->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 255)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 255)->nullable();
            });
        }

        if (!Schema::hasTable('m_blast_rule_recipient')) {
            Schema::create('m_blast_rule_recipient', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email', 255)->nullable();
                $table->string('name', 255)->nullable();
                $table->unsignedInteger('rule_id')->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 255)->nullable();
                $table->timestamp('updated_at')->useCurrent();
                $table->string('updated_who', 255)->nullable();
            });
        }

        if (!Schema::hasTable('m_email_sync_table')) {
            Schema::create('m_email_sync_table', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('brand_id')->nullable();
                $table->string('table_name', 100)->nullable();
                $table->string('email_column', 100)->nullable();
                $table->string('name_column', 100)->nullable();
                $table->string('where_clause', 100)->nullable();
                $table->string('where_value', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 255)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 255)->nullable();
            });
        }

        if (!Schema::hasTable('mt_customer_email')) {
            Schema::create('mt_customer_email', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('brand_id')->nullable();
                $table->string('nama', 255)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('no_batch', 100)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
                $table->string('status_blast', 255)->nullable();
            });
        }

        if (!Schema::hasTable('t_content')) {
            Schema::create('t_content', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('brand_id')->nullable();
                $table->text('body_content')->nullable();
                $table->string('date_namespace', 8)->nullable();
                $table->string('time_namespace', 8)->nullable();
                $table->text('materi_name')->nullable();
                $table->text('subject')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('t_asset')) {
            Schema::create('t_asset', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('content_id')->nullable();
                $table->string('name', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('t_blast_log')) {
            Schema::create('t_blast_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('brand_id')->nullable();
                $table->unsignedInteger('content_id')->nullable();
                $table->unsignedInteger('last_email_id')->nullable();
                $table->string('status', 20)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('m_user')) {
            Schema::create('m_user', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_name', 100)->nullable();
                $table->text('real_name')->nullable();
                $table->string('password', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('created_who', 100)->nullable();
                $table->timestamp('updated_at')->useCurrent();
                $table->string('updated_who', 100)->nullable();
                $table->string('flag', 1)->default('Y')->nullable();
            });
        }

        if (!Schema::hasTable('t_token_user')) {
            Schema::create('t_token_user', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->string('token', 255)->nullable();
                $table->timestamp('login_at')->useCurrent();
                $table->timestamp('logout_at')->nullable();
            });
        }

        // Pastikan 2 akun (admin untuk Live Production & dev untuk Local/Staging) tersedia
        if (Schema::hasTable('m_user')) {
            DB::table('m_user')
                ->where('user_name', 'admin')
                ->update([
                    'real_name' => 'Live Production Admin',
                    'flag'      => 'Y',
                ]);

            $devExists = DB::table('m_user')->where('user_name', 'dev')->exists();
            if (!$devExists) {
                DB::table('m_user')->insert([
                    'user_name'   => 'dev',
                    'real_name'   => 'Local Staging Tester',
                    'password'    => 'Y2p5', // Encrypted '123' via AES-128-CTR
                    'created_at'  => now(),
                    'created_who' => 'System',
                    'updated_at'  => now(),
                    'updated_who' => 'System',
                    'flag'        => 'Y',
                ]);
            }
        }
    }

    public function down()
    {
        // Dikosongkan secara sengaja untuk mencegah penghapusan tabel eksisting
    }
}

