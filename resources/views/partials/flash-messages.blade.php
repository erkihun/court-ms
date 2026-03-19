@if(session('success'))
<div class="ui-alert ui-alert-success mb-4">
    <x-heroicon-o-check-circle class="h-5 w-5 mt-0.5 shrink-0" />
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="ui-alert ui-alert-error mb-4">
    <x-heroicon-o-x-circle class="h-5 w-5 mt-0.5 shrink-0" />
    {{ session('error') }}
</div>
@endif
