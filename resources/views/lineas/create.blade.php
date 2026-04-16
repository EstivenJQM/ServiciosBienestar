<x-layout title="Nueva Línea">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Nueva Línea" color="sibi">
                <form action="{{ route('lineas.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="id_componente" class="form-label fw-semibold">
                            Componente <span class="text-danger">*</span>
                        </label>
                        <select id="id_componente" name="id_componente" required
                                class="form-select {{ $errors->has('id_componente') ? 'is-invalid' : '' }}">
                            <option value="">-- Seleccione un componente --</option>
                            @foreach($componentes as $componente)
                                <option value="{{ $componente->id_componente }}"
                                    {{ old('id_componente') == $componente->id_componente ? 'selected' : '' }}>
                                    {{ $componente->area->nombre }} › {{ $componente->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_componente')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        placeholder="Ej: Salud Mental"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.actions :back="route('lineas.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
