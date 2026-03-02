@php
use Illuminate\Support\Facades\Storage;

$value = $getState();
$url = null;
$isPdf = false;

if ($value) {
$url = Storage::disk('public')->url($value);
$isPdf = strtolower(pathinfo($value, PATHINFO_EXTENSION)) === 'pdf';
}
@endphp

<div class="space-y-2">
    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $getLabel() }}
    </div>

    @if ($url)
    @if ($isPdf)
    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
        style="height: 600px;">
        <iframe
            src="{{ $url }}"
            class="w-full h-full"
            style="height: 600px;"
            type="application/pdf"
            frameborder="0">
            <p class="p-4 text-sm text-gray-500">
                Browser Anda tidak mendukung tampilan PDF inline.
            </p>
        </iframe>
    </div>
    @else
    <img
        src="{{ $url }}"
        alt="Denah Persil"
        class="max-w-full rounded-lg border border-gray-200 dark:border-gray-700 object-contain"
        style="max-height: 600px;" />
    @endif

    <a href="{{ $url }}" target="_blank"
        class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:underline">
        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="w-4 h-4" />
        Buka di tab baru
    </a>
    @else
    <p class="text-sm text-gray-400 italic">-</p>
    @endif
</div>