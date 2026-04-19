<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MasterData\ComplaintTypeResource;
use App\Http\Resources\Api\V1\MasterData\ProgramResource;
use App\Http\Resources\Api\V1\MasterData\RegionResource;
use App\Models\ComplaintType;
use App\Models\District;
use App\Models\Program;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class MasterDataController extends Controller
{
    public function programs(): AnonymousResourceCollection
    {
        $programs = Cache::remember('master.programs', now()->addHours(24), function () {
            return Program::where('is_active', true)->orderBy('name')->get();
        });

        return ProgramResource::collection($programs);
    }

    public function provinces(): AnonymousResourceCollection
    {
        $provinces = Cache::remember('master.provinces', now()->addHours(24), function () {
            return Province::where('is_selectable', true)->orderBy('name')->get();
        });

        return RegionResource::collection($provinces);
    }

    public function regencies(int $province): AnonymousResourceCollection
    {
        $regencies = Cache::remember("master.regencies.{$province}", now()->addHours(24), function () use ($province) {
            return Regency::where('province_id', $province)
                ->where('is_selectable', true)
                ->orderBy('name')
                ->get();
        });

        return RegionResource::collection($regencies);
    }

    public function districts(int $regency): AnonymousResourceCollection
    {
        $districts = Cache::remember("master.districts.{$regency}", now()->addHours(24), function () use ($regency) {
            return District::where('regency_id', $regency)->orderBy('name')->get();
        });

        return RegionResource::collection($districts);
    }

    public function villages(int $district): AnonymousResourceCollection
    {
        $villages = Cache::remember("master.villages.{$district}", now()->addHours(24), function () use ($district) {
            return Village::where('district_id', $district)->orderBy('name')->get();
        });

        return RegionResource::collection($villages);
    }

    public function complaintTypes(): AnonymousResourceCollection
    {
        $types = Cache::remember('master.complaint-types', now()->addHours(24), function () {
            return ComplaintType::where('is_active', true)->orderBy('name')->get();
        });

        return ComplaintTypeResource::collection($types);
    }
}
