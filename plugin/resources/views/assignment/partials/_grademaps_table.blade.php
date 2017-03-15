<div>
    @if (!$charon->grademaps->isEmpty())

        <h2 class="title">{{ translate('grades') }}</h2>

        <table class="table is-bordered">
            <thead>
            <tr>
                <th>{{ translate('grade_name') }}</th>
                <th>{{ translate('max_points') }}</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($charon->grademaps as $grademap)
                <tr>
                    <td>{{ $grademap->name }}</td>
                    <td>{{ floatval($grademap->gradeItem->grademax) }}p</td>
                </tr>
            @endforeach
            </tbody>

        </table>

    @endif
</div>
