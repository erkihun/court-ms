<x-public-layout title="Home">
    <div class="max-w-3xl">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Welcome to the Court Public Portal</h1>
        <p class="mt-4 text-slate-600">
            Search public case information and view basic details.
        </p>

        <form method="GET" action="{{ route('public.cases') }}" class="mt-6 flex gap-2">
            <input name="q" placeholder="Search case number, title, court, type…"
                class="w-full px-3 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>
</x-public-layout>