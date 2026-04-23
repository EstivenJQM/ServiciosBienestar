<x-layout title="Nuevo Cargo">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Nuevo Cargo" color="sibi">
                <form action="{{ route('cargos.store') }}" method="POST">
                    @csrf

                    <x-form.input
                        name="codigo"
                        label="Código"
                        placeholder="Ej: 21"
                        :maxlength="30"
                        autofocus
                    />

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        placeholder="Ej: DOCENTE PLANTA"
                        :maxlength="150"
                    />

                    <x-form.actions :back="route('cargos.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
