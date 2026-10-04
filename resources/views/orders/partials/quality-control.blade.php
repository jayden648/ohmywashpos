{{-- Quality control. The server refuses to release an order for pickup
     until every item here has passed. --}}
<section class="omw-card border-brand p-4 ring-1 ring-brand/40">
    <h2 class="mb-1 text-base font-bold text-ink">Quality Control</h2>
    <p class="mb-3 text-xs text-gray-500">
        Semua checklist wajib lolos sebelum pesanan dapat ditandai “Siap Diambil”.
    </p>

    <form method="POST" action="{{ route('orders.quality-control', $order) }}" class="space-y-3">
        @csrf

        @foreach ($qualityChecks as $check)
            <label class="flex items-start gap-2.5 text-sm text-ink">
                <input type="checkbox" name="checks[{{ $check->value }}]" value="1"
                       @checked(($qc[$check->value] ?? false) === true)
                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand focus:ring-brand">
                <span>{{ $check->label() }}</span>
            </label>
        @endforeach

        @error('checks.*')
            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror

        <div>
            <label for="qc-notes" class="omw-label">Catatan QC</label>
            <textarea id="qc-notes" name="notes" rows="2" class="omw-input">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="omw-btn-primary w-full">Simpan Hasil QC</button>
    </form>
</section>