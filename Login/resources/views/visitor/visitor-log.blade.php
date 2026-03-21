<x-studentLayout>
    {{-- Global Small Light Gray Scrollbar Styles --}}
    <style>
        /* Small light gray scrollbars for all elements */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
            transition: background 0.2s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        ::-webkit-scrollbar-corner {
            background: #f8f9fa;
        }

        /* Firefox scrollbar styling */
        * {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f8f9fa;
        }
    </style>

        <div class="w-full max-w-2xl p-10 mx-auto mt-8 bg-white border border-gray-200 shadow-2xl rounded-3xl animate-fadeIn">

            {{-- Header --}}
            <div class="flex flex-col items-center justify-center mb-6 space-y-4 text-center sm:flex-row animate-bounceIn sm:space-y-0 sm:space-x-4">

                {{-- Title --}}
                <h1 class="text-2xl font-extrabold text-orange-700 sm:text-3xl">Visitors Log Form</h1>
            </div>

            {{-- Back Link --}}
            <a href="{{ route('visitor.dashboard.show') }}" class="inline-flex items-center mb-8 text-blue-700 hover:underline">
                <i data-feather="arrow-left" class="w-5 h-5 mr-2"></i>
            </a>

            {{-- Form --}}
            <form action="{{ route('visitor.store') }}" method="POST" class="space-y-4">
                @csrf
                @error('error')
                    <div class="text-red-500">{{ $message }}</div>
                @enderror
                <div>
                    <label for="guard_id" class="block mb-2 font-semibold text-gray-700">Guard ID <span class="text-red-500">*</span></label>
                    <input type="text" id="guard_id" name="guard_id"
                        value="{{ old('guard_id') }}"
                        class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('visitor_name') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}">
                    <x-input-error :messages="$errors->get('guard_id')" class="mt-2 text-sm text-red-500" />
                </div>
                <div>
                    <label for="visitor_name" class="block mb-2 font-semibold text-gray-700">Visitor's Name <span class="text-red-500">*</span></label>
                    <input type="text" id="visitor_name" name="visitor_name"
                        value="{{ old('visitor_name') }}"
                        class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('visitor_name') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}">
                    <x-input-error :messages="$errors->get('visitor_name')" class="mt-2 text-sm text-red-500" />
                </div>

                <div>
                    <label for="valid_id" class="block mb-2 font-semibold text-gray-700">ID Type <span class="text-red-500">*</span></label>
                    <select name="valid_id" id="valid_id" class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('valid_id') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}" onchange="toggleOtherInput(this.value)">
                        <option value="" disabled selected>Select ID Type</option>
                        <option value="Student ID" {{ old('valid_id') == 'Student ID' ? 'selected' : '' }}>Student ID</option>
                        <option value="Employee ID" {{ old('valid_id') == 'Employee ID' ? 'selected' : '' }}>Employee ID</option>
                        <option value="Government ID" {{ old('valid_id') == 'Government ID' ? 'selected' : '' }}>Government ID</option>
                        <option value="National ID" {{ old('valid_id') == 'National ID' ? 'selected' : '' }}>National ID</option>
                        <option value="Passport" {{ old('valid_id') == 'Passport' ? 'selected' : '' }}>Passport</option>
                        <option value="Driver's License" {{ old('valid_id') == 'Driver\'s License' ? 'selected' : '' }}>Driver's License</option>
                        <option value="SSS/GSIS ID" {{ old('valid_id') == 'SSS/GSIS ID' ? 'selected' : '' }}>SSS/GSIS ID</option>
                        <option value="PhilHealth ID" {{ old('valid_id') == 'PhilHealth ID' ? 'selected' : '' }}>PhilHealth ID</option>
                        <option value="Postal ID" {{ old('valid_id') == 'Postal ID' ? 'selected' : '' }}>Postal ID</option>
                        <option value="Company ID" {{ old('valid_id') == 'Company ID' ? 'selected' : '' }}>Company ID</option>
                        <option value="Barangay ID" {{ old('valid_id') == 'Barangay ID' ? 'selected' : '' }}>Barangay ID</option>
                        <option value="PRC ID" {{ old('valid_id') == 'PRC ID' ? 'selected' : '' }}>PRC ID (for professionals)</option>
                        <option value="Senior Citizen ID" {{ old('valid_id') == 'Senior Citizen ID' ? 'selected' : '' }}>Senior Citizen ID / PWD ID</option>
                        <option value="Other" {{ old('valid_id') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <x-input-error :messages="$errors->get('valid_id')" class="mt-2 text-sm text-red-500" />
                </div>

                <div id="other_id_type" class="hidden">
                    <label for="other_id_type_input" class="block mb-2 font-semibold text-gray-700">Specify ID Type <span class="text-red-500">*</span></label>
                    <input type="text" id="other_id_type_input" name="other_id_type"
                        value="{{ old('other_id_type') }}"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    <x-input-error :messages="$errors->get('other_id_type')" class="mt-2 text-sm text-red-500" />
                </div>

                <div>
                    <label for="id_number" class="block mb-2 font-semibold text-gray-700">ID Number <span class="text-red-500">*</span></label>
                    <input type="number" id="id_number" name="id_number"
                        value="{{ old('id_number') }}"
                        class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('id_number') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}">
                    <x-input-error :messages="$errors->get('id_number')" class="mt-2 text-sm text-red-500" />
                </div>

                {{-- Relationship --}}
                <div>
                    <label for="relationship" class="block mb-2 font-semibold text-gray-700">Relationship</label>
                    <input type="text" id="relationship" name="relationship"
                        value="{{ old('relationship') }}"
                        class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('relationship') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}">
                    <x-input-error :messages="$errors->get('relationship')" class="mt-2 text-sm text-red-500" />
                </div>

                {{-- Purpose --}}
                <div>
                    <label for="purpose" class="block mb-2 font-semibold text-gray-700">Purpose<span class="font-normal text-">(Student or Staff to Visit) <span class="text-red-500">*</span></span></label>
                    <input type="text" id="purpose" name="purpose"
                        value="{{ old('purpose') }}"
                        class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('purpose') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}">
                    <x-input-error :messages="$errors->get('purpose')" class="mt-2 text-sm text-red-500" />
                </div>

                @if ($errors->has('visitor_pass'))
                    <div class="mt-1 text-sm text-red-500">
                        {{ $errors->first('visitor_pass') }}
                    </div>
                @endif

                {{-- Submit Button --}}
                <div class="pt-8 text-center">
                    <button type="submit"
                        class="inline-flex items-center gap-2 py-2 font-semibold text-orange-500 transition duration-300 ease-in-out bg-orange-200 border-2 border-orange-300 rounded-md shadow-lg px-7 hover:bg-orange-500 hover:text-white hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-300">
                        Submit
                    </button>
                </div>

            </form>
        </div>
    {{-- Custom Animations --}}
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out forwards;
        }
        .animate-bounceIn {
            animation: bounceIn 1s ease-out forwards;
        }

        /* Media Query for Mobile Screens */
        @media screen and (max-width: 768px) {
            .inline-flex {
                font-size: 14px;
                padding: 10px 16px;
            }
            .px-8 {
                padding-left: 24px;
                padding-right: 24px;
            }
            .py-4 {
                padding-top: 10px;
                padding-bottom: 10px;
            }
        }
    </style>

    <script>
        function toggleOtherInput(value) {
            const otherInputDiv = document.getElementById('other_id_type');
            const otherInput = document.getElementById('other_id_type_input');

            if (value === 'Other') {
                otherInputDiv.classList.remove('hidden');
                otherInput.setAttribute('required', 'required');
            } else {
                otherInputDiv.classList.add('hidden');
                otherInput.removeAttribute('required');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const validIdSelect = document.getElementById('valid_id');
            toggleOtherInput(validIdSelect.value);
        });
    </script>
</x-studentLayout>
