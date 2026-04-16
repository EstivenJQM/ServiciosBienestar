<x-layout title="Nuevo Componente">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color:#3369b3">
                    <h5 class="mb-0">Nuevo Componente</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('componentes.store') }}" method="POST">
                        @csrf

                        <x-form.select
                            name="id_area"
                            label="Área"
                            :options="$areas"
                            placeholder="-- Seleccione un área --"
                        />

                        <x-form.input
                            name="nombre"
                            label="Nombre"
                            placeholder="Ej: Psicosocial"
                            :maxlength="150"
                            autofocus
                        />

                        <x-form.actions :back="route('componentes.index')" label="Guardar" />
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout>
