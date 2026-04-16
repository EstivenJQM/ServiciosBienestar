<x-layout title="Nueva Área">

    <div class="row justify-content-center">
        <div class="col-md-6">
            <x-card title="Nueva Área" color="sibi">
                <form action="{{ route('areas.store') }}" method="POST">
                    @csrf

                    <x-form.input
                        name="nombre"
                        label="Nombre"
                        placeholder="Ej: Dirección de Bienestar Institucional"
                        :maxlength="150"
                        autofocus
                    />

                    <x-form.actions :back="route('areas.index')" label="Guardar" />
                </form>
            </x-card>
        </div>
    </div>

</x-layout>
