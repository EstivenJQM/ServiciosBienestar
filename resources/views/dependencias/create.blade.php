<x-layout title="Nueva Dependencia">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Nueva Dependencia" color="sibi">
                <form action="{{ route('dependencias.store') }}" method="POST">
                    @csrf

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        placeholder="Ej: DIRECCIÓN DE GESTIÓN HUMANA"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.actions :back="route('dependencias.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
