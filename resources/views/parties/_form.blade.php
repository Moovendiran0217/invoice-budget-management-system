<form method="POST" action="{{ $action }}" class="card mt-6 grid gap-4 p-5 md:grid-cols-2">
    @csrf
    @isset($method)
        @method($method)
    @endisset
    <div>
        <label class="field-label">Name</label>
        <input class="field" name="name" value="{{ old('name', $record?->name ?? '') }}" required>
    </div>
    <div>
        <label class="field-label">Email</label>
        <input class="field" type="email" name="email" value="{{ old('email', $record?->email ?? '') }}">
    </div>
    <div>
        <label class="field-label">Phone</label>
        <input class="field" name="phone" value="{{ old('phone', $record?->phone ?? '') }}">
    </div>
    <div>
        <label class="field-label">GSTIN</label>
        <input class="field" name="gstin" value="{{ old('gstin', $record?->gstin ?? '') }}">
    </div>
    <div>
        <label class="field-label">City</label>
        <input class="field" name="city" value="{{ old('city', $record?->city ?? '') }}">
    </div>
    <div class="md:col-span-2">
        <label class="field-label">Address</label>
        <textarea class="field" name="address" rows="3">{{ old('address', $record?->address ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="field-label">Notes</label>
        <textarea class="field" name="notes" rows="2">{{ old('notes', $record?->notes ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <button class="btn btn-primary">Save</button>
        <a class="btn btn-secondary" href="{{ $cancel }}">Cancel</a>
    </div>
</form>
