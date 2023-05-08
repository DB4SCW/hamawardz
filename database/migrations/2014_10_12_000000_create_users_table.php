<?php

use App\Models\User;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('username')->unique();;
            $table->string('password');
            $table->boolean('siteadmin')->default(false);
            $table->boolean('cancreateevents')->default(false);
            $table->boolean('locked')->default(false);
        });

        //create initial administration user
        $admin = new User();
        $admin->username = 'administrator';
        $admin->password = bcrypt('welcome#01');
        $admin->siteadmin = true;
        $admin->save();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
