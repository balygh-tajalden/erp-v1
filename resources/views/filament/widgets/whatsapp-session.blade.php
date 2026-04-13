<div
    wire:poll.4000ms="checkStatus"
    class="fi-wi-whatsapp-session">
    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- الحالة: متصل  --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if ($status === 'CONNECTED')
    <div class="flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-5 py-4 shadow-sm dark:border-green-500/20 dark:bg-green-950/30">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500/20">
            <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5l-4-4 1.41-1.41L10 13.67l6.59-6.58L18 8.5l-8 8z" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-green-600 dark:text-green-400">واتساب متصل ✓</p>
            <p class="text-xs text-green-500/80 dark:text-green-500/60">
                الرقم: <span class="font-mono font-bold">{{ $phoneNumber ?? 'غير معروف' }}</span> — الجلسة: <span class="font-mono">{{ $sessionName }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
            </span>
            <span class="text-[10px] text-green-600/70">نشط</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- الحالة: جارٍ التشغيل أو القائمة (Spinner) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @elseif ($status === 'STARTING')
    <div class="flex items-center gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 py-4">
        <div class="flex h-10 w-10 items-center justify-center">
            <svg class="h-7 w-7 animate-spin text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">جارٍ تشغيل الجلسة...</p>
            <p class="text-xs text-amber-500/80">انتظر لحظة حتى يتم تجهيز الاتصال</p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- الحالة: عرض QR Code --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @elseif ($status === 'QRCODE')
    <div class="flex flex-col items-center gap-4 rounded-xl border border-blue-500/30 bg-blue-500/5 px-6 py-6 dark:border-blue-500/20 dark:bg-blue-950/20">
        <div class="text-center">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">امسح رمز الـ QR لربط الحساب</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                افتح تطبيق واتساب ← الأجهزة المرتبطة ← ربط جهاز
            </p>
        </div>

        @if ($qrCode)
        <div class="rounded-2xl border-4 border-white p-2 shadow-xl dark:border-gray-700 bg-white">
            <img
                src="{{ $qrCode }}"
                alt="WhatsApp QR Code"
                class="h-56 w-56 rounded-xl object-contain" />
        </div>
        @else
        <div class="flex h-56 w-56 flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
            <svg class="h-8 w-8 animate-spin text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
            </svg>
            <p class="mt-2 text-[10px] text-gray-400">جاري جلب الرمز...</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- الحالة: خطأ --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @elseif ($status === 'ERROR')
    <div class="flex items-center gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/20">
            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-red-600 dark:text-red-400">فشل الاتصال بالسيرفر</p>
            <p class="text-xs text-red-500/80">{{ $errorMsg ?? 'تأكد من تشغيل خدمة الواتساب المستقلة.' }}</p>
        </div>
        <button
            wire:click="checkStatus"
            class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-600">
            تحديث
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- الحالة: لا توجد جلسة (إضافة جلسة) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @else
    <div class="flex items-center justify-between rounded-xl border border-dashed border-gray-300 bg-white px-6 py-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-500">
                <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">ربط جلسة واتساب</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    قم بإضافة جلسة جديدة للبدء في إرسال الإشعارات التلقائية
                </p>
            </div>
        </div>

        <button
            wire:click="startSession"
            wire:loading.attr="disabled"
            class="rounded-lg bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-600 disabled:opacity-50">
            <span wire:loading.remove wire:target="startSession">إضافة جلسة جديدة +</span>
            <span wire:loading wire:target="startSession">جارٍ البدء...</span>
        </button>
    </div>
    @endif
</div>