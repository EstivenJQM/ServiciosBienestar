<x-layout title="Editar Dependencia">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Editar Dependencia" color="warning">
                <form action="{{ route('dependencias.update', $dependencia) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        :value="$dependencia->nombre"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.actions
                        :back="route('dependencias.index')"
                        label="Actualizar"
                        color="warning"
                    />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
