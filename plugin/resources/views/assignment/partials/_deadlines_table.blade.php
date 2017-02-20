<div>
    <h2 class="title">Deadlines</h2>

    <table class="table is-bordered">
        <thead>
        <tr>
            <th>Deadline</th>
            <th>Percentage</th>
        </tr>
        </thead>
        <tbody>

        @if ($charon->deadlines->isEmpty())
            <tr><td colspan="2">No deadlines!</td></tr>
        @endif

        @foreach ($charon->deadlines as $deadline)
            <tr>
                <td>{{ $deadline->deadline_time }}</td>
                <td>{{ $deadline->percentage }}%</td>
            </tr>
        @endforeach
        </tbody>

    </table>

</div>
