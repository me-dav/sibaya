@if (session('status'))
    <div role="status" class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">{{ session('status') }}</div>
@endif
@if (session('error'))
    <div role="alert" class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">{{ session('error') }}</div>
@endif
