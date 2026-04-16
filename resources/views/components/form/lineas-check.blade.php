@props(['componentes', 'seleccionadas' => []])

<div class="mb-3">
    <label class="form-label fw-semibold">
        Líneas asociadas <span class="text-danger">*</span>
    </label>

    @if($componentes->isEmpty())
        <p class="text-muted small">No hay líneas disponibles.</p>
    @else
        <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
            @foreach($componentes as $componente)
                @if($componente->lineas->isNotEmpty())
                    {{-- Cabecera Área › Componente --}}
                    <p class="mb-1 mt-2 small fw-semibold text-muted">
                        <span class="badge" style="background-color:#3369b3;font-size:.7rem">{{ $componente->area->nombre }}</span>
                        <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i>
                        <span class="badge" style="background-color:#6f42c1;font-size:.7rem">{{ $componente->nombre }}</span>
                    </p>

                    @foreach($componente->lineas as $linea)
                        <div class="form-check ms-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="lineas[]"
                                value="{{ $linea->id_linea }}"
                                id="linea_{{ $linea->id_linea }}"
                                {{ in_array($linea->id_linea, old('lineas', $seleccionadas)) ? 'checked' : '' }}
                            >
                            <label class="form-check-label small" for="linea_{{ $linea->id_linea }}">
                                {{ $linea->nombre }}
                            </label>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>

        @error('lineas')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error('lineas.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    @endif
</div>
