<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BillingApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Complaint\StoreRequest;
use App\Http\Resources\Api\V1\ComplaintListResource;
use App\Http\Resources\Api\V1\ComplaintResource;
use App\Models\Complaint;
use App\Services\CustomerLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    public function __construct(
        protected CustomerLookupService $customerLookupService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $complaints = Complaint::ownedBy($request->user()->id)
            ->with('complaintType')
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 15), 100)));

        return ComplaintListResource::collection($complaints);
    }

    public function show(Request $request, int $complaint): JsonResponse
    {
        $record = Complaint::ownedBy($request->user()->id)
            ->with('complaintType')
            ->findOrFail($complaint);

        return (new ComplaintResource($record))
            ->response()
            ->setStatusCode(200);
    }

    public function timeline(Request $request, int $complaint): JsonResponse
    {
        $workOrderRelations = [
            'meterRepair', 'meterReplacement', 'meterClosed', 'meterReopening',
            'meterDisconnection', 'meterAddressChange', 'meterNameChange', 'meterRateChange', 'meterTera',
        ];

        $beritaAcaraRelations = [
            'repairReport', 'teraMeterReport', 'meterReplacementHandover',
            'statusChange', 'subscriptionCancellation', 'subscriptionClosure', 'subscriptionReopening',
        ];

        $record = Complaint::ownedBy($request->user()->id)
            ->with(array_merge(['followUps'], $workOrderRelations, $beritaAcaraRelations))
            ->findOrFail($complaint);

        $events = [];

        $events[] = [
            'type' => 'complaint_created',
            'at' => $record->created_at?->toIso8601String(),
            'payload' => [
                'no_pengaduan' => $record->no_pengaduan,
                'judul_pengaduan' => $record->judul_pengaduan,
                'status' => $record->status,
                'priority' => $record->priority,
            ],
        ];

        foreach ($record->followUps->sortBy('follow_up_at') as $followUp) {
            $events[] = [
                'type' => 'follow_up',
                'at' => $followUp->follow_up_at?->toIso8601String(),
                'payload' => [
                    'id' => $followUp->id,
                    'work_order' => $followUp->work_order?->value,
                    'notes' => $followUp->notes,
                    'photos' => $followUp->photos,
                    'carbon_copies' => $followUp->carbon_copies,
                    'created_at' => $followUp->created_at?->toIso8601String(),
                ],
            ];
        }

        $workOrderCandidates = [
            ['jenis' => 'perbaikan', 'relation' => 'meterRepair', 'nomor_field' => 'no_spp', 'details_fn' => fn ($m) => ['keluhan' => $m->keluhan, 'tindakan_perbaikan' => $m->tindakan_perbaikan]],
            ['jenis' => 'ganti_meter', 'relation' => 'meterReplacement', 'nomor_field' => 'no_spgm', 'details_fn' => fn ($m) => ['alasan_penggantian' => $m->alasan_penggantian, 'biaya_ganti_meter' => $m->biaya_ganti_meter]],
            ['jenis' => 'tutup', 'relation' => 'meterClosed', 'nomor_field' => 'no_sptl', 'details_fn' => fn ($m) => ['alasan_tutup' => $m->alasan_tutup]],
            ['jenis' => 'buka_kembali', 'relation' => 'meterReopening', 'nomor_field' => 'no_spbk', 'details_fn' => fn ($m) => ['alasan_buka_kembali' => $m->alasan_buka_kembali, 'biaya_buka_kembali' => $m->biaya_buka_kembali]],
            ['jenis' => 'cabut', 'relation' => 'meterDisconnection', 'nomor_field' => 'no_spcl', 'details_fn' => fn ($m) => ['alasan_cabut' => $m->alasan_cabut]],
            ['jenis' => 'ubah_alamat', 'relation' => 'meterAddressChange', 'nomor_field' => 'no_spua', 'details_fn' => fn ($m) => ['alasan_ubah_alamat' => $m->alasan_ubah_alamat, 'biaya_ubah_alamat' => $m->biaya_ubah_alamat]],
            ['jenis' => 'ubah_nama', 'relation' => 'meterNameChange', 'nomor_field' => 'no_spun', 'details_fn' => fn ($m) => ['alasan_ubah_nama' => $m->alasan_ubah_nama, 'nama_lama' => $m->nama_lama, 'nama_baru' => $m->nama_baru]],
            ['jenis' => 'ganti_tarif', 'relation' => 'meterRateChange', 'nomor_field' => 'no_sput', 'details_fn' => fn ($m) => ['alasan_ganti_tarif' => $m->alasan_ganti_tarif]],
            ['jenis' => 'tera_meter', 'relation' => 'meterTera', 'nomor_field' => 'no_spp', 'details_fn' => fn ($m) => ['keluhan' => $m->keluhan, 'hasil_tera_meter' => $m->hasil_tera_meter]],
        ];

        foreach ($workOrderCandidates as $candidate) {
            $model = $record->{$candidate['relation']};

            if ($model !== null) {
                $events[] = [
                    'type' => 'work_order',
                    'at' => $model->tanggal?->toIso8601String(),
                    'payload' => [
                        'jenis' => $candidate['jenis'],
                        'nomor' => $model->{$candidate['nomor_field']},
                        'nama_pegawai' => $model->nama_pegawai ?? null,
                        'no_sambungan' => $model->no_sambungan,
                        'nama' => $model->nama,
                        'alamat' => $model->alamat,
                        'latitude' => $model->latitude ?? null,
                        'longitude' => $model->longitude ?? null,
                        'details' => ($candidate['details_fn'])($model),
                    ],
                ];

                if (in_array($candidate['jenis'], ['ubah_nama', 'ubah_alamat', 'ganti_tarif']) && $model->is_confirmed) {
                    $events[] = [
                        'type' => 'confirmed',
                        'at' => $model->updated_at?->toIso8601String(),
                        'payload' => [
                            'jenis' => $candidate['jenis'],
                            'nomor' => $model->{$candidate['nomor_field']},
                        ],
                    ];
                }

                break;
            }
        }

        $beritaAcaraCandidates = [
            ['jenis' => 'perbaikan', 'relation' => 'repairReport', 'nomor_field' => 'no_bap', 'details_fn' => fn ($m) => ['items' => $m->items, 'catatan' => $m->catatan]],
            ['jenis' => 'tera_meter', 'relation' => 'teraMeterReport', 'nomor_field' => 'no_bap', 'details_fn' => fn ($m) => ['items' => $m->items, 'catatan' => $m->catatan]],
            ['jenis' => 'ganti_meter', 'relation' => 'meterReplacementHandover', 'nomor_field' => 'no_bast_gm', 'details_fn' => fn ($m) => ['merk_wm_lama' => $m->merk_wm_lama, 'no_wm_lama' => $m->no_wm_lama, 'merk_wm_baru' => $m->merk_wm_baru, 'no_wm_baru' => $m->no_wm_baru]],
            ['jenis' => 'ubah_status', 'relation' => 'statusChange', 'nomor_field' => 'no_baus', 'details_fn' => fn ($m) => ['jenis_rumah' => $m->jenis_rumah, 'jumlah_kran' => $m->jumlah_kran, 'daya_listrik' => $m->daya_listrik, 'klasifikasi_sr' => $m->klasifikasi_sr, 'catatan' => $m->catatan]],
            ['jenis' => 'cabut', 'relation' => 'subscriptionCancellation', 'nomor_field' => 'no_bacl', 'details_fn' => fn ($m) => ['catatan' => $m->catatan]],
            ['jenis' => 'tutup', 'relation' => 'subscriptionClosure', 'nomor_field' => 'no_batl', 'details_fn' => fn ($m) => ['catatan' => $m->catatan]],
            ['jenis' => 'buka_kembali', 'relation' => 'subscriptionReopening', 'nomor_field' => 'no_bast_bk', 'details_fn' => fn ($m) => ['catatan' => $m->catatan]],
        ];

        foreach ($beritaAcaraCandidates as $candidate) {
            $model = $record->{$candidate['relation']};

            if ($model !== null) {
                $events[] = [
                    'type' => 'berita_acara',
                    'at' => $model->tanggal?->toIso8601String(),
                    'payload' => [
                        'jenis' => $candidate['jenis'],
                        'nomor' => $model->{$candidate['nomor_field']},
                        'no_sambungan' => $model->no_sambungan,
                        'nama' => $model->nama,
                        'alamat' => $model->alamat,
                        'lokasi' => $model->lokasi ?? null,
                        'foto_sebelum' => $model->foto_sebelum ?? null,
                        'foto_sesudah' => $model->foto_sesudah ?? null,
                        'details' => ($candidate['details_fn'])($model),
                    ],
                ];

                break;
            }
        }

        usort($events, function (array $a, array $b): int {
            if ($a['at'] === null) {
                return 1;
            }
            if ($b['at'] === null) {
                return -1;
            }

            return strcmp($a['at'], $b['at']);
        });

        return response()->json(['data' => $events], 200);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Auto-fill nama & alamat from billing data (FR27)
        try {
            $billing = $this->customerLookupService->fetchByNoSambungan($data['no_sambungan'], throwOnError: true);
        } catch (BillingApiException) {
            return response()->json([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ], 503);
        }

        $billingData = $billing['data'] ?? null;
        $data['nama'] = $billingData['nama_pelanggan'] ?? $request->user()->name;
        $data['alamat'] = $billingData['alamat_pelanggan'] ?? null;

        // Auto-set fields (FR28)
        $data['user_id'] = $request->user()->id;
        $data['sumber'] = 'mobile_apps';
        $data['tanggal'] = now();

        $complaint = DB::transaction(function () use ($data) {
            return Complaint::create($data);
        });

        $complaint->refresh();
        $complaint->load('complaintType');

        return (new ComplaintResource($complaint))
            ->response()
            ->setStatusCode(201);
    }
}
