@php
$applicant = auth('applicant')->user();
@endphp

@if($applicant && !$applicant->hasVerifiedEmail())
<div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="text-sm text-amber-900">
            <strong>Verify your email</strong> — We sent a verification link to
            <span class="font-medium">{{ $applicant->email }}</span>. Please check your inbox (and spam).
        </div>

        <form method="POST" action="{{ route('applicant.verification.send') }}" class="flex-shrink-0">
            @csrf
            <button
                class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 bg-white px-3 py-1.5 text-sm font-medium text-amber-900 hover:bg-amber-100">
                {{-- refresh icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 4v6h6M20 20v-6h-6M20 9A8 8 0 1 0 9 20" />
                </svg>
                Resend link
            </button>
        </form>
    </div>
</div>
@endif