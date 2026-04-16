<x-layout title="Nuevo Tipo de Actividad">

    <div class="row justify-content-center">
        <div class="col-md-7">
            <x-card title="Nuevo Tipo de Actividad" color="sibi">
                <form action="{{ route('tipo-actividad.store') }}" method="POST">
                    @csrf

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        placeholder="Ej: Taller"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.lineas-check :componentes="$componentes" />

                    <x-form.actions :back="route('tipo-actividad.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
