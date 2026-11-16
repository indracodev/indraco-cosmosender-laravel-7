@props([
    'id' => 'password',
    'name' => 'password',
    'placeholder' => '',
    'required' => false,
])

<div class="input-group">
    <input
        type="password"
        id="{{ $id }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control']) }}
    >
    <button
        type="button"
        class="btn border"
        data-toggle-password
        data-target="#{{ $id }}"
        aria-label="Tampilkan password"
        title="Tampilkan / Sembunyikan Password"
    >
        <i class="bi bi-eye"></i>
    </button>
</div>