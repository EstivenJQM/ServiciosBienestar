<x-layout title="Nuevo Programa">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Nuevo Programa" color="sibi">
                <form action="{{ route('programas.store') }}" method="POST">
                    @csrf

                    <x-form.input
                        name="nombre"
                        label="Nombre del programa"
                        placeholder="Ej: Ingeniería de Sistemas"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.select
                        name="id_facultad"
                        label="Facultad"
                        :options="$facultades"
                        keyField="id_facultad"
                        placeholder="-- Seleccione una facultad --"
                    />

                    <div class="mb-3">
                        <label for="id_tipo_formacion" class="form-label fw-semibold">
                            Tipo de formación
                            <span class="text-muted fw-normal small">(opcional)</span>
                        </label>
                        <select id="id_tipo_formacion" name="id_tipo_formacion"
                                class="form-select {{ $errors->has('id_tipo_formacion') ? 'is-invalid' : '' }}">
                            <option value="">-- Sin especificar --</option>
                            @foreach($niveles as $nivel)
                                <optgroup label="{{ $nivel->nombre }}">
                                    @foreach($nivel->tiposFormacion as $tipo)
                                        <option value="{{ $tipo->id_tipo_formacion }}"
                                            {{ old('id_tipo_formacion') == $tipo->id_tipo_formacion ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('id_tipo_formacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-form.sedes-snies :sedes="$sedes" />

                    <x-form.actions :back="route('programas.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
