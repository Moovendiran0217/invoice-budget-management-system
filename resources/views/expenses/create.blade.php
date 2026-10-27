@component('layouts.app', ['title' => 'Add expense'])
    <h1 class="page-title">Add expense</h1>
    @include('expenses._form', ['action' => route('expenses.store'), 'expense' => null])
@endcomponent
