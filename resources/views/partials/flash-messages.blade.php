@if(session('success'))
<div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-3 py-2">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 rounded-md bg-red-100 border border-red-300 text-red-800 px-3 py-2">
    {{ session('error') }}
</div>
@endif