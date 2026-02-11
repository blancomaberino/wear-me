<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $suggestions = DB::table('outfit_suggestions')
            ->whereNotNull('garment_ids')
            ->get();

        foreach ($suggestions as $suggestion) {
            $garmentIds = json_decode($suggestion->garment_ids, true);
            if (!is_array($garmentIds)) {
                continue;
            }

            foreach ($garmentIds as $index => $garmentId) {
                // Only insert if the garment still exists
                $exists = DB::table('garments')->where('id', $garmentId)->exists();
                if ($exists) {
                    DB::table('outfit_suggestion_garment')->insert([
                        'outfit_suggestion_id' => $suggestion->id,
                        'garment_id' => $garmentId,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('outfit_suggestion_garment')->truncate();
    }
};
