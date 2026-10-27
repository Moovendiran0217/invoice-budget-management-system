@component('layouts.app', ['title' => 'Edit budget'])
    <h1 class="page-title">Edit {{ $budget->name }}</h1>
    @include('budgets._form', ['action' => route('budgets.update', $budget), 'method' => 'PUT', 'budget' => $budget])
@endcomponent
