@component('layouts.app', ['title' => 'Edit expense'])
    <h1 class="page-title">Edit expense</h1>
    @include('expenses._form', ['action' => route('expenses.update', $expense), 'method' => 'PUT', 'expense' => $expense])
@endcomponent
