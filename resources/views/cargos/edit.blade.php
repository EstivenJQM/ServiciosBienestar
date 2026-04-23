<x-layout title="Editar Cargo">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Editar Cargo" color="warning">
                <form action="{{ route('cargos.update', $cargo) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <x-form.input
                        name="codigo"
                        label="Código"
                        :value="$cargo->codigo"
                        :maxlength="30"
                        autofocus
                    />

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        :value="$cargo->nombre"
                        :maxlength="150"
                    />

                    <x-form.actions
                        :back="route('cargos.index')"
                        label="Actualizar"
                        color="warning"
                    />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
