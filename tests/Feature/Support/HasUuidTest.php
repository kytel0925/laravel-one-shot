<?php

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

beforeEach(function () {
    Schema::create('has_uuid_probes', function (Blueprint $table) {
        $table->id();
        $table->uuid()->unique();
    });
});

function hasUuidProbe(): Model
{
    return new class extends Model
    {
        use HasUuid;

        protected $table = 'has_uuid_probes';

        public $timestamps = false;
    };
}

test('it generates a time-ordered uuid on create', function () {
    $model = hasUuidProbe();
    $model->save();

    expect(Str::isUuid($model->uuid, 7))->toBeTrue()
        ->and($model->getRouteKeyName())->toBe('uuid');
});

test('it keeps a uuid that was set explicitly', function () {
    $uuid = (string) Str::uuid7();

    $model = hasUuidProbe();
    $model->setAttribute('uuid', $uuid);
    $model->save();

    expect($model->uuid)->toBe($uuid);
});
