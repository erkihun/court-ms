@php($applicant = auth('applicant')->user())
@if($applicant && !$applicant->hasVerifiedEmail())
<div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-md">
    {{ __('Please verify your email address to access all applicant portal features.') }}
</div>
@endif
