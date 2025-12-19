<x-applicant-layout title="Apply - Register">
    <h1 class="text-2xl font-semibold mb-4">Applicant Registration</h1>

    <form method="POST" action="{{ route('applicant.register.submit') }}" class="bg-white p-6 rounded-lg border space-y-4">
        @csrf

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block ">First name</label>
                <input name="first_name" value="{{ old('first_name') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('first_name') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block ">Middle name</label>
                <input name="middle_name" value="{{ old('middle_name') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('middle_name') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block ">Last name</label>
                <input name="last_name" value="{{ old('last_name') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('last_name') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block ">Gender</label>
                <select name="gender" class="mt-1 w-full px-3 py-2 border rounded">
                    <option value="">-- Select --</option>
                    @foreach(['male','female','other'] as $g)
                        <option value="{{ $g }}" @selected(old('gender')===$g)>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
                @error('gender') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block ">Phone</label>
                <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('phone') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block ">National ID Number</label>
                <input name="national_id_number" value="{{ old('national_id_number') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('national_id_number') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block ">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('email') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block ">Address</label>
                <input name="address" value="{{ old('address') }}" class="mt-1 w-full px-3 py-2 border rounded">
                @error('address') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block ">Password</label>
                <input name="password" type="password" class="mt-1 w-full px-3 py-2 border rounded">
                @error('password') <p class="text-red-600 ">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block ">Confirm Password</label>
                <input name="password_confirmation" type="password" class="mt-1 w-full px-3 py-2 border rounded">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Create account</button>
            <a href="{{ route('applicant.login') }}" class=" text-blue-700 hover:underline">Already registered? Sign in</a>
        </div>
    </form>
</x-applicant-layout>
