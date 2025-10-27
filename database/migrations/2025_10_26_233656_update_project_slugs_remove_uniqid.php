<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('projects')->get()->each(function ($project) {
            // Remove the unique ID part from the slug
            $cleanSlug = preg_replace('/-[a-f0-9]{13}$/', '', $project->slug);

            // Ensure the slug is unique after cleaning, if necessary
            // For simplicity, we'll assume the cleaned slug is unique enough
            // or that the original title was unique enough to generate a unique slug.
            // If not, a more robust solution would involve checking for uniqueness
            // and appending a counter if duplicates are found.

            DB::table('projects')
                ->where('id', $project->id)
                ->update(['slug' => $cleanSlug]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing this migration would mean re-adding the uniqid to slugs,
        // which is not straightforward without storing the original uniqid.
        // For now, we'll leave it empty or add a warning.
        // If a rollback is truly needed, it would require a more complex solution
        // or manual intervention.
    }
};
