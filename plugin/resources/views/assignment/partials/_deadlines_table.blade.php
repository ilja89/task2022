<div>
    @if (! $charon->deadlines->isEmpty())

        <h2 class="title">Deadlines</h2>

        <table class="table is-bordered">
            <thead>
            <tr>
                <th>After</th>
                <th>Percentage</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($charon->deadlines as $deadline)
                <tr>
                    <td>{{ $deadline->deadline_time->format('d/m/Y H:i') }}</td>
                    <td>{{ $deadline->percentage }}%</td>
                </tr>
            @endforeach
            </tbody>

        </table>

    @endif

</div>
