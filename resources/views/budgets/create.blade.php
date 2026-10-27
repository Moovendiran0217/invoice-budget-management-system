@component('layouts.app', ['title' => 'Create budget'])
    <h1 class="page-title">Create budget</h1>
    @include('budgets._form', ['action' => route('budgets.store'), 'budget' => null])
@endcomponent
