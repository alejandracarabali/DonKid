{{-- Grupos con las materias o dimensiones que dicta el docente. Se usa en Notas y en Grupos. --}}
<ul class="space-y-3">
    @foreach ($classrooms as $classroom)
        <li class="card">
            <h3 class="text-[17px] font-bold">{{ $classroom->name }}</h3>
            <ul class="mt-2 flex flex-wrap gap-2">
                @foreach ($classroom->subjects as $subject)
                    <li>
                        <a href="{{ route('educator.grades.show', [$classroom, $subject]) }}"
                           class="inline-flex min-h-[44px] items-center rounded-xl border-2 border-line px-3 font-bold hover:border-action">{{ $subject->name }}</a>
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>
