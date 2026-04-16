<x-layout title="Nueva Facultad">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Nueva Facultad" color="sibi">
                <form action="{{ route('facultades.store') }}" method="POST">
                    @csrf

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        placeholder="Ej: Facultad de Ingeniería"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.checkboxes
                        name="sedes"
                        label="Sedes"
                        :items="$sedes"
                        keyField="id_sede"
                        subLabel="codigo"
                    />

                    <x-form.actions :back="route('facultades.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
