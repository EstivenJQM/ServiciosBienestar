<x-layout title="Nuevo Servicio">

    <div class="row justify-content-center">
        <div class="col-md-7">
            <x-card title="Nuevo Servicio" color="sibi">
                <form action="{{ route('servicios.store') }}" method="POST">
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control {{ $errors->has('nombre') ? 'is-invalid' : '' }}"
                               value="{{ old('nombre') }}"
                               placeholder="Ej: Taller de habilidades blandas"
                               maxlength="200"
                               autofocus>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Línea --}}
                    <div class="mb-3">
                        <label for="id_linea" class="form-label fw-semibold">
                            Línea <span class="text-danger">*</span>
                        </label>
                        <select id="id_linea" name="id_linea" required
                                class="form-select {{ $errors->has('id_linea') ? 'is-invalid' : '' }}">
                            <option value="">-- Seleccione una línea --</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}"
                                    {{ old('id_linea') == $linea->id_linea ? 'selected' : '' }}>
                                    {{ $linea->componente->area->nombre }}
                                    › {{ $linea->componente->nombre }}
                                    › {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_linea')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo de Actividad (dinámico según línea) --}}
                    <div class="mb-3">
                        <label for="id_tipo_actividad" class="form-label fw-semibold">
                            Tipo de Actividad <span class="text-danger">*</span>
                        </label>
                        <select id="id_tipo_actividad" name="id_tipo_actividad" required
                                class="form-select {{ $errors->has('id_tipo_actividad') ? 'is-invalid' : '' }}"
                                disabled>
                            <option value="">-- Primero seleccione una línea --</option>
                        </select>
                        @error('id_tipo_actividad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sede --}}
                    <div class="mb-3">
                        <label for="id_sede" class="form-label fw-semibold">
                            Sede <span class="text-danger">*</span>
                        </label>
                        <select id="id_sede" name="id_sede" required
                                class="form-select {{ $errors->has('id_sede') ? 'is-invalid' : '' }}">
                            <option value="">-- Seleccione una sede --</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}"
                                    {{ old('id_sede') == $sede->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sede')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha --}}
                    <div class="mb-3">
                        <label for="fecha" class="form-label fw-semibold">
                            Fecha <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="fecha" name="fecha"
                               class="form-control {{ $errors->has('fecha') ? 'is-invalid' : '' }}"
                               value="{{ old('fecha') }}">
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Período --}}
                    <div class="mb-3">
                        <label for="id_periodo" class="form-label fw-semibold">
                            Período <span class="text-danger">*</span>
                        </label>
                        <select id="id_periodo" name="id_periodo" required
                                class="form-select {{ $errors->has('id_periodo') ? 'is-invalid' : '' }}">
                            <option value="">-- Seleccione un período --</option>
                            @foreach($periodos as $periodo)
                                <option value="{{ $periodo->id_periodo }}"
                                    {{ old('id_periodo') == $periodo->id_periodo ? 'selected' : '' }}>
                                    {{ $periodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_periodo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-form.actions :back="route('servicios.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

    {{-- Mapa de línea → tipos de actividad para el select dinámico --}}
    @php
        $lineasMap = $lineas->mapWithKeys(fn($l) => [
            $l->id_linea => $l->tiposActividad->map(fn($t) => [
                'id'     => $t->id_tipo_actividad,
                'nombre' => $t->nombre,
            ])->values(),
        ]);
    @endphp

    <script>
        const lineasMap = @json($lineasMap);
        const oldTipoActividad = "{{ old('id_tipo_actividad') }}";

        const selectLinea = document.getElementById('id_linea');
        const selectTipo  = document.getElementById('id_tipo_actividad');

        function actualizarTipos(idLinea, preselect) {
            selectTipo.innerHTML = '';
            const tipos = lineasMap[idLinea] ?? [];

            if (tipos.length === 0) {
                selectTipo.disabled = true;
                selectTipo.innerHTML = '<option value="">-- Sin tipos de actividad --</option>';
                return;
            }

            selectTipo.disabled = false;
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Seleccione un tipo --';
            selectTipo.appendChild(placeholder);

            tipos.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nombre;
                if (String(t.id) === String(preselect)) opt.selected = true;
                selectTipo.appendChild(opt);
            });
        }

        selectLinea.addEventListener('change', function () {
            actualizarTipos(this.value, null);
        });

        // Restaurar estado al volver con errores (old values)
        if (selectLinea.value) {
            actualizarTipos(selectLinea.value, oldTipoActividad);
        }
    </script>

</x-layout>
