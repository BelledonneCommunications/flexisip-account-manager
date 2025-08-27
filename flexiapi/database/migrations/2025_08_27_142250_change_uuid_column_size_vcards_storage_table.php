<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\VcardStorage;
use Sabre\VObject;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vcards_storage', function (Blueprint $table) {
            $table->string('uuid', 45)->change();
        });

        foreach (VcardStorage::all() as $vcardStorage) {
            $vcard = VObject\Reader::read($vcardStorage->vcard);
            $vcardStorage->uuid = $vcard->UID;
            $vcardStorage->save();
        }
    }

    public function down(): void
    {
        Schema::table('vcards_storage', function (Blueprint $table) {
            $table->string('uuid', 36)->change();
        });
    }
};
