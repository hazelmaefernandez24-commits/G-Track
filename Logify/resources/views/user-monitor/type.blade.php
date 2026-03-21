<x-monitorLayout>
    <script src="https://unpkg.com/feather-icons"></script>
    <div class="min-h-screen pt-1">
        <div class="relative p-8 mx-auto space-y-8 bg-white rounded-lg shadow-md max-w-10xl">

            <a href="{{ route('monitor.dashboard') }}" class="absolute flex items-center font-semibold text-blue-500 top-4 left-4 hover:text-blue-700">
                <i data-feather="arrow-left" class="mr-2"></i>
            </a>
            {{-- {{ dd($type) }} --}}
            <h2 class="text-3xl font-bold text-center text-orange-700">
                @if ($type === 'GoingOut')
                    Set Leisure Schedule
                @elseif ($type === 'Academic')
                    Set Academic Regular Schedule
                @elseif ($type === 'Irregular')
                    Set Academic Irregular Schedule
                @endif
            </h2>

            {{-- @dump(request()->all()); --}}
            <form method="GET" action="{{ route('monitor.schedule') }}">
                <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                    @if ($type === 'GoingOut')
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div>
                            <label for="gender" class="block mb-1 text-sm font-semibold text-gray-700">Select Gender</label>
                            <select name="gender" id="gender" onchange="this.form.submit()"
                                class="w-full p-2 bg-gray-100 border-2 border-gray-300 rounded focus:outline-none focus:border-orange-500">
                                <option value="">-- Select Gender --</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender }}" {{ request('batch') == $gender ? 'selected' : '' }}>
                                        {{ $gender == 'M' ? 'Male' : 'Female' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($type === 'Academic')
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div>
                            <label for="batch" class="block mb-1 text-sm font-semibold text-gray-700">Select Batch</label>
                            <select name="batch" id="batch"
                                onchange="if(this.value && document.querySelector('select[name=group]').value) this.form.submit();"
                                class="w-full p-2 bg-gray-100 border-2 border-gray-300 rounded focus:outline-none focus:border-orange-500">
                                <option value="">-- Select Class --</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                                        Class {{ $batch }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="group" class="block mb-1 text-sm font-semibold text-gray-700">Select Group</label>
                            <select name="group" id="group"
                                onchange="if(this.value && document.querySelector('select[name=batch]').value) this.form.submit();"
                                class="w-full p-2 bg-gray-100 border-2 border-gray-300 rounded focus:outline-none focus:border-orange-500">
                                <option value="">-- Select Group --</option>
                                <option value="PN1" {{ request('group') == 'PN1' ? 'selected' : '' }}>PN1</option>
                                <option value="PN2" {{ request('group') == 'PN2' ? 'selected' : '' }}>PN2</option>
                            </select>
                        </div>
                    @elseif ($type === 'Irregular')
                        <div class="col-span-2">
                            <label for="batch" class="block mb-1 text-sm font-semibold text-gray-700">Select Class</label>
                            <select name="batch" id="batch"
                                class="w-full p-2 bg-gray-100 border-2 border-gray-300 rounded focus:outline-none focus:border-orange-500">
                                <option value="">-- Select Class --</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                                        Class {{ $batch }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    @endif
                </div>
            </form>
        </div>
    </div>
</x-monitorLayout>
