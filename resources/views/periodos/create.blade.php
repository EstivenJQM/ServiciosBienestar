<x-layout title="Nuevo Período">

    <div class="row justify-content-center">
        <div class="col-md-5">
            <x-card title="Nuevo Período" color="sibi">
                <form action="{{ route('periodos.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">
                            Período <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control {{ $errors->has('nombre') ? 'is-invalid' : '' }}"
                               value="{{ old('nombre') }}"
                               placeholder="Ej: 2025-1"
                               maxlength="6"
                               autofocus>
                        <div class="form-text">Formato: YYYY-S (año-semestre). Ej: 2025-1, 2025-2</div>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-form.actions :back="route('periodos.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
